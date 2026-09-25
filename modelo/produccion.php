<?php
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/recetas.php";
require_once __DIR__ . "/materia_prima.php";
require_once __DIR__ . "/inventario.php";

class Produccion {

    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    // $id_producto = producto a producir (ej: Torta Chocolate 1/4)
    // $cantidad_producida = ej: 7
    public function producir($id_producto, $cantidad_producida) {

        $receta = new Receta();
        $materiaPrima = new MateriaPrima();
        $inventario = new Inventario();

        $insumos = $receta->obtener_por_producto($id_producto);

        if (empty($insumos)) {
            return ['ok' => false, 'mensaje' => 'Este producto no tiene receta registrada'];
        }

        // 1. Verificar stock suficiente ANTES de descontar nada
        $faltantes = [];
        foreach ($insumos as $insumo) {
            $necesario = $insumo['cantidad'] * $cantidad_producida;
            if ($insumo['stock_disponible'] < $necesario) {
                $faltantes[] = $insumo['nombre_insumo'] . " (necesitas {$necesario}, tienes {$insumo['stock_disponible']})";
            }
        }

        if (!empty($faltantes)) {
            return ['ok' => false, 'mensaje' => 'Materia prima insuficiente: ' . implode(', ', $faltantes)];
        }

        // 2. Transacción: descuenta materia prima y aumenta inventario juntos
        try {
            $this->db->beginTransaction();

            foreach ($insumos as $insumo) {
                $necesario = $insumo['cantidad'] * $cantidad_producida;
                $materiaPrima->descontar_stock($insumo['id_materia_prima'], $necesario);
            }

            $inventario->aumentar($id_producto, $cantidad_producida);

            $this->db->commit();

            return ['ok' => true, 'mensaje' => "Se produjeron {$cantidad_producida} unidades y se descontó la materia prima"];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['ok' => false, 'mensaje' => 'Error al producir: ' . $e->getMessage()];
        }
    }
}
?>

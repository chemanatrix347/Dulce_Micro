<?php
// modelo/informes.php
require_once __DIR__ . '/../config/conexion.php';

class Informes {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    /**
     * Trae las transacciones (tabla_maestra) del rango de fechas indicado,
     * ya relacionadas con cliente, estado del pedido y el movimiento contable.
     * Usa BETWEEN con parametros ligados (PDO), nunca concatenacion directa.
     */
    public function obtener_transacciones($desde, $hasta) {
        $sql = "SELECT
                    tm.id_maestro,
                    tm.fecha_transaccion,
                    c.nombre AS cliente,
                    tm.cantidad_pedida,
                    ep.estado_pedido,
                    ct.tipo_movimiento,
                    ct.monto_transaccion
                FROM tabla_maestra tm
                INNER JOIN clientes c ON c.id_cliente = tm.id_cliente
                INNER JOIN estado_pedido ep ON ep.id_estado_pedido = tm.id_estado_pedido
                INNER JOIN contabilidad ct ON ct.id_contabilidad = tm.id_contabilidad
                WHERE tm.estado = 1
                  AND tm.fecha_transaccion BETWEEN :desde AND :hasta
                ORDER BY tm.fecha_transaccion ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':desde', $desde);
        $stmt->bindParam(':hasta', $hasta);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Suma de los montos del periodo, para mostrar un total en pantalla,
     * PDF y CSV sin tener que recorrer el arreglo cada vez.
     */
    public function total_periodo($desde, $hasta) {
        $sql = "SELECT COALESCE(SUM(ct.monto_transaccion), 0) AS total
                FROM tabla_maestra tm
                INNER JOIN contabilidad ct ON ct.id_contabilidad = tm.id_contabilidad
                WHERE tm.estado = 1
                  AND tm.fecha_transaccion BETWEEN :desde AND :hasta";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':desde', $desde);
        $stmt->bindParam(':hasta', $hasta);
        $stmt->execute();
        return (float) $stmt->fetchColumn();
    }
}
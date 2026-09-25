<?php
// controlador/FacturaPDF.php

session_name("LOGIN");
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../vista/login.php");
    exit();
}

require_once __DIR__ . "/../modelo/pedidocompleto.php";
require_once __DIR__ . "/../lib/fpdf/fpdf.php";

$idPedido = $_GET['id_pedido'] ?? '';
if ($idPedido === '') {
    die("Falta el numero de pedido.");
}

$pedidoModel = new PedidoCompleto();
$factura = $pedidoModel->datos_factura($idPedido);

if (!$factura) {
    die("No se encontro el pedido para generar la factura (verifica que este activo).");
}

// Ruta del logo en disco (mismo archivo que se usa en el sidebar/header)
$logoPath = __DIR__ . '/../img_logos/logo principal.png';

class FacturaPDF extends FPDF
{
    public $logoPath;

    function Header()
    {
        if ($this->logoPath && file_exists($this->logoPath)) {
            $anchoLogo = 26;
            $x = (210 - $anchoLogo) / 2;
            $this->Image($this->logoPath, $x, 8, $anchoLogo);
            $this->SetY(8 + 22);
        } else {
            $this->SetY(12);
        }

        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(168, 121, 201); // morado de marca
        $this->Cell(0, 8, 'Dulce Micro', 0, 1, 'C');

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(130, 130, 130);
        $this->Cell(0, 5, 'Reposteria artesanal', 0, 1, 'C');

        $this->Ln(3);
        $this->SetDrawColor(230, 133, 168); // rosa fuerte
        $this->SetLineWidth(0.6);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(6);
    }

    function Footer()
    {
        $this->SetY(-20);
        $this->SetDrawColor(230, 133, 168);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 5, 'Gracias por su compra - Dulce Micro', 0, 1, 'C');
        $this->SetY(-14);
        $this->Cell(0, 5, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new FacturaPDF();
$pdf->logoPath = $logoPath;
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTextColor(0, 0, 0);

// ---- Encabezado de la factura ----
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 7, 'Factura de venta No. ' . str_pad($factura['id_pedido'], 6, '0', STR_PAD_LEFT), 0, 0, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(95, 7, 'Fecha: ' . date('d/m/Y', strtotime($factura['fecha_pedido'])), 0, 1, 'R');
$pdf->Ln(3);

// ---- Datos del cliente ----
$pdf->SetFillColor(247, 168, 196); // rosa de marca
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, ' Datos del cliente', 0, 1, 'L', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Nombre: ' . $factura['nombre_cliente'], 0, 1);
$pdf->Cell(0, 6, 'Documento: ' . $factura['numero_documento'], 0, 1);
if (!empty($factura['telefono'])) {
    $pdf->Cell(0, 6, 'Telefono: ' . $factura['telefono'], 0, 1);
}
$pdf->Ln(4);

// ---- Tabla de producto ----
$pdf->SetFillColor(201, 160, 220); // morado de marca
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(85, 8, 'Producto', 1, 0, 'L', true);
$pdf->Cell(25, 8, 'Cantidad', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Precio unit.', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Subtotal', 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(85, 8, $factura['nombre_producto'], 1, 0, 'L');
$pdf->Cell(25, 8, $factura['cantidad'], 1, 0, 'C');
$pdf->Cell(35, 8, '$' . number_format($factura['precio_unitario'], 0, ',', '.'), 1, 0, 'R');
$pdf->Cell(35, 8, '$' . number_format($factura['subtotal'], 0, ',', '.'), 1, 1, 'R');
$pdf->Ln(4);

// ---- Total y metodo de pago ----
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(145, 8, 'Metodo de pago: ' . $factura['metodo_pago'], 0, 0, 'L');
$pdf->Cell(35, 8, 'TOTAL: $' . number_format($factura['subtotal'], 0, ',', '.'), 0, 1, 'R');

// 'I' = mostrar en el navegador (nueva pestaña); usa 'D' si prefieres forzar descarga
$pdf->Output('I', 'factura_pedido_' . $factura['id_pedido'] . '.pdf');
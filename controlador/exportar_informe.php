<?php
// controlador/exportar_informe.php
session_name("LOGIN");
session_start();

// Proteccion de acceso: igual que el resto de los modulos, sin sesion no hay reporte
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../vista/login.php");
    exit();
}

require_once __DIR__ . "/../modelo/informes.php";
$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/../vista/partials/restringir_roles.php';

function fechaValida_Exportar($fecha) {
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

if (!fechaValida_Exportar($desde)) $desde = date('Y-m-01');
if (!fechaValida_Exportar($hasta)) $hasta = date('Y-m-d');

if ($desde > $hasta) {
    [$desde, $hasta] = [$hasta, $desde];
}

$tipo = $_GET['tipo'] ?? '';

$informes = new Informes();
$transacciones = $informes->obtener_transacciones($desde, $hasta);
$total = $informes->total_periodo($desde, $hasta);

// ---------------------------------------------------------------
// CSV: delimitado por ";" (mejor compatibilidad con Excel en español)
// con BOM UTF-8 al inicio para que tildes y "ñ" se vean bien
// ---------------------------------------------------------------
if ($tipo === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="informe_' . $desde . '_a_' . $hasta . '.csv"');

    $salida = fopen('php://output', 'w');
    fwrite($salida, "\xEF\xBB\xBF"); // BOM UTF-8

    fputcsv($salida, ['ID', 'Fecha', 'Cliente', 'Cantidad', 'Estado pedido', 'Movimiento', 'Monto'], ';');

    foreach ($transacciones as $fila) {
        fputcsv($salida, [
            $fila['id_maestro'],
            $fila['fecha_transaccion'],
            $fila['cliente'],
            $fila['cantidad_pedida'],
            $fila['estado_pedido'],
            $fila['tipo_movimiento'],
            number_format((float) $fila['monto_transaccion'], 2, ',', '.'),
        ], ';');
    }
    fputcsv($salida, ['', '', '', '', '', 'Total', number_format($total, 2, ',', '.')], ';');

    fclose($salida);
    exit();
}

// ---------------------------------------------------------------
// PDF: usa la libreria FPDF (un solo archivo, sin composer).
// Descargala de http://www.fpdf.org/en/dl.php y coloca fpdf.php en:
//   Dulce_Micro/lib/fpdf/fpdf.php
// ---------------------------------------------------------------
if ($tipo === 'pdf') {
    if (!file_exists(__DIR__ . '/../lib/fpdf/fpdf.php')) {
        http_response_code(500);
        die('Falta la libreria FPDF. Descargala de http://www.fpdf.org/en/dl.php y coloca fpdf.php en Dulce_Micro/lib/fpdf/fpdf.php');
    }
    require_once __DIR__ . '/../lib/fpdf/fpdf.php';

    // FPDF (sin el addon UTF-8) solo entiende ISO-8859-1.
    // utf8_decode() esta deprecado desde PHP 8.2 y su warning rompe los
    // encabezados del PDF, asi que convertimos con iconv en su lugar.
    function texto_pdf($texto) {
        $convertido = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', (string) $texto);
        return $convertido !== false ? $convertido : (string) $texto;
    }

    class InformePDF extends FPDF {
        public $desde = '';
        public $hasta = '';
        public $logoPath = '';
        private $anchos = [15, 22, 50, 18, 30, 30, 25];

        function Header() {
            // ---- Logo del comercio, centrado, arriba de todo ----
            if ($this->logoPath && file_exists($this->logoPath)) {
                $anchoLogo = 28; // mm
                $medidas = @getimagesize($this->logoPath);
                $altoLogo = $medidas ? $anchoLogo * ($medidas[1] / $medidas[0]) : $anchoLogo;
                $x = ($this->GetPageWidth() - $anchoLogo) / 2;
                $this->Image($this->logoPath, $x, 8, $anchoLogo);
                $this->SetY(8 + $altoLogo + 3);
            }

            $this->SetFont('Arial', 'B', 14);
            $this->Cell(0, 8, 'Dulce Micro - Informe de Transacciones', 0, 1, 'C');
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 6, 'Periodo: ' . $this->desde . ' a ' . $this->hasta, 0, 1, 'C');
            $this->Ln(4);

            $this->SetFillColor(33, 37, 41);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Arial', 'B', 9);
            $titulos = ['ID', 'Fecha', 'Cliente', 'Cant.', 'Estado', 'Movimiento', 'Monto'];
            foreach ($titulos as $i => $titulo) {
                $this->Cell($this->anchos[$i], 8, texto_pdf($titulo), 1, 0, 'C', true);
            }
            $this->Ln();
            $this->SetTextColor(0, 0, 0);
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . ' de {nb}', 0, 0, 'C');
        }

        function fila($datos) {
            foreach ($datos as $i => $valor) {
                $alineacion = $i === 6 ? 'R' : ($i === 3 ? 'C' : 'L');
                $this->Cell($this->anchos[$i], 7, texto_pdf((string) $valor), 1, 0, $alineacion);
            }
            $this->Ln();
        }
    }

    $pdf = new InformePDF();
    $pdf->desde = $desde;
    $pdf->hasta = $hasta;
    $pdf->logoPath = __DIR__ . '/../img_logos/logo principal.png';
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 9);

    foreach ($transacciones as $t) {
        $pdf->fila([
            $t['id_maestro'],
            $t['fecha_transaccion'],
            $t['cliente'],
            $t['cantidad_pedida'],
            $t['estado_pedido'],
            $t['tipo_movimiento'],
            '$' . number_format((float) $t['monto_transaccion'], 2, ',', '.'),
        ]);
    }

    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, texto_pdf('Total del periodo: $' . number_format($total, 2, ',', '.')), 0, 1, 'R');

    $pdf->Output('D', 'informe_' . $desde . '_a_' . $hasta . '.pdf');
    exit();
}

// Sin tipo valido: regresamos a la vista
header("Location: ../vista/informes.php?desde=$desde&hasta=$hasta");
exit();
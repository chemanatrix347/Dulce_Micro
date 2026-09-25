<?php
// controlador/exportar_materiaprima.php
session_name("LOGIN");
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../vista/login.php");
    exit();
}


$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/../vista/partials/restringir_roles.php';

require_once __DIR__ . "/../modelo/materiaprima.php";
require_once __DIR__ . "/../modelo/unidadmedida.php";

$materiaPrima = new MateriaPrima();
$lista = $materiaPrima->leer_materias_primas();

$unidadMedidaModel = new UnidadMedida();
$nombresUnidad = array_column($unidadMedidaModel->leer_unidades_medida(), 'unidad_medida', 'id_unidad_medida');

$tipo = $_GET['tipo'] ?? '';

// ---------------------------------------------------------------
// CSV: delimitado por ";" con BOM UTF-8 para tildes/enie en Excel
// ---------------------------------------------------------------
if ($tipo === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="materia_prima_' . date('Y-m-d') . '.csv"');

    $salida = fopen('php://output', 'w');
    fwrite($salida, "\xEF\xBB\xBF");

    fputcsv($salida, ['ID', 'Insumo', 'Descripcion', 'Stock disponible', 'Unidad de medida'], ';');

    foreach ($lista as $fila) {
        fputcsv($salida, [
            $fila['id_materia_prima'],
            $fila['nombre_insumo'],
            $fila['descripcion'],
            $fila['stock_disponible'],
            $nombresUnidad[$fila['id_unidad_medida']] ?? $fila['id_unidad_medida'],
        ], ';');
    }

    fclose($salida);
    exit();
}

// ---------------------------------------------------------------
// PDF: reutiliza la misma libreria FPDF de Informes
// ---------------------------------------------------------------
if ($tipo === 'pdf') {
    if (!file_exists(__DIR__ . '/../lib/fpdf/fpdf.php')) {
        http_response_code(500);
        die('Falta la libreria FPDF en Dulce_Micro/lib/fpdf/fpdf.php');
    }
    require_once __DIR__ . '/../lib/fpdf/fpdf.php';

    if (!function_exists('texto_pdf')) {
        function texto_pdf($texto) {
            $convertido = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', (string) $texto);
            return $convertido !== false ? $convertido : (string) $texto;
        }
    }

    class MateriaPrimaPDF extends FPDF {
        public $logoPath = '';
        private $anchos = [15, 45, 70, 22, 33];

        function Header() {
            if ($this->logoPath && file_exists($this->logoPath)) {
                $anchoLogo = 28;
                $medidas = @getimagesize($this->logoPath);
                $altoLogo = $medidas ? $anchoLogo * ($medidas[1] / $medidas[0]) : $anchoLogo;
                $x = ($this->GetPageWidth() - $anchoLogo) / 2;
                $this->Image($this->logoPath, $x, 8, $anchoLogo);
                $this->SetY(8 + $altoLogo + 3);
            }

            $this->SetFont('Arial', 'B', 14);
            $this->Cell(0, 8, 'Dulce Micro - Inventario de Materia Prima', 0, 1, 'C');
            $this->Ln(4);

            $this->SetFillColor(33, 37, 41);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Arial', 'B', 9);
            $titulos = ['ID', 'Insumo', 'Descripcion', 'Stock', 'Unidad de medida'];
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

        // Calcula cuantas lineas ocupara un texto dentro de un ancho dado
        // (patron oficial de la documentacion de FPDF para filas de alto variable)
        function NbLines($w, $txt) {
            $cw = $this->CurrentFont['cw'];
            if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
            $s = str_replace("\r", '', (string) $txt);
            $nb = strlen($s);
            if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
            $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
            while ($i < $nb) {
                $c = $s[$i];
                if ($c == "\n") {
                    $i++; $sep = -1; $j = $i; $l = 0; $nl++;
                    continue;
                }
                if ($c == ' ') $sep = $i;
                $l += $cw[$c] ?? 600;
                if ($l > $wmax) {
                    if ($sep == -1) {
                        if ($i == $j) $i++;
                    } else {
                        $i = $sep + 1;
                    }
                    $sep = -1; $j = $i; $l = 0; $nl++;
                } else {
                    $i++;
                }
            }
            return $nl;
        }

        // Dibuja una fila completa con alto ajustado a la celda que mas texto tenga,
        // usando MultiCell en cada columna para que el texto no se desborde
        function fila($datos) {
            $lineHeight = 5;
            $nbMax = 1;
            $textos = [];
            foreach ($datos as $i => $valor) {
                $textos[$i] = texto_pdf((string) $valor);
                $nbMax = max($nbMax, $this->NbLines($this->anchos[$i], $textos[$i]));
            }
            $altoFila = $nbMax * $lineHeight;

            if ($this->GetY() + $altoFila > $this->PageBreakTrigger) {
                $this->AddPage($this->CurOrientation);
            }

            $x = $this->GetX();
            $y = $this->GetY();
            foreach ($textos as $i => $texto) {
                $w = $this->anchos[$i];
                $alineacion = ($i === 3 || $i === 4) ? 'C' : 'L';
                $this->Rect($x, $y, $w, $altoFila);
                $this->SetXY($x, $y);
                $this->MultiCell($w, $lineHeight, $texto, 0, $alineacion);
                $x += $w;
            }
            $this->SetXY($this->lMargin, $y + $altoFila);
        }
    }

    $pdf = new MateriaPrimaPDF();
    $pdf->logoPath = __DIR__ . '/../img_logos/logo principal.png';
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 9);

    foreach ($lista as $m) {
        $pdf->fila([
            $m['id_materia_prima'],
            $m['nombre_insumo'],
            $m['descripcion'],
            $m['stock_disponible'],
            $nombresUnidad[$m['id_unidad_medida']] ?? $m['id_unidad_medida'],
        ]);
    }

    $pdf->Output('D', 'materia_prima_' . date('Y-m-d') . '.pdf');
    exit();
}

header("Location: ../vista/materiaprima.php");
exit();
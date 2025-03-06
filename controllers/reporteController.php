<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/asistencia/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asistencia/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asistencia/models/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Verifica si la opción es generar el reporte
if (isset($_GET['option']) && $_GET['option'] === 'generarReporteExcel') {
    try {
        // Instancia de la clase de conexión y obtención del PDO
        $conexion = new Conexion();
        $conn = $conexion->conectar();

     
       $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

        // Construye la consulta con filtros de fecha
        $query = "
            SELECT 
                a.id, 
                a.ingreso, 
                a.salida, 
                a.fecha, 
                CONCAT(e.nombre, ' ', e.apellido) AS nombre_completo, 
                a.estado 
            FROM asistencias a
            JOIN estudiantes e ON a.id_estudiante = e.id
        ";

        // Añade condiciones al filtro si las fechas están presentes
        if ($fechaInicio && $fechaFin) {
            $query .= " WHERE a.fecha BETWEEN :fechaInicio AND :fechaFin";
        }

        $stmt = $conn->prepare($query);

        // Enlaza los parámetros de fechas si existen
        if ($fechaInicio && $fechaFin) {
            $stmt->bindParam(':fechaInicio', $fechaInicio);
            $stmt->bindParam(':fechaFin', $fechaFin);
        }

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // (Resto del código para generar el reporte Excel igual que antes)
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Reporte de Asistencias");

        $headers = ['ID', 'Ingreso', 'Salida', 'Fecha', 'Nombre Completo', 'Estado'];

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        // Agrega encabezados
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("$colIndex" . "1", $header);
            $colIndex++;
        }

        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        $rowIndex = 2;
        foreach ($data as $row) {
            $sheet->setCellValue("A$rowIndex", $row['id']);
            $sheet->setCellValue("B$rowIndex", $row['ingreso']);
            $sheet->setCellValue("C$rowIndex", $row['salida']);
            $sheet->setCellValue("D$rowIndex", $row['fecha']);
            $sheet->setCellValue("E$rowIndex", $row['nombre_completo']);
            $sheet->setCellValue("F$rowIndex", $row['estado']);
            $rowIndex++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle("A1:F" . ($rowIndex - 1))->applyFromArray($borderStyle);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="reporte_asistencias.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;

    } catch (PDOException $e) {
        echo "Error al conectar con la base de datos: " . $e->getMessage();
    } finally {
        $conn = null; // Cierra la conexión
    }
}

?>

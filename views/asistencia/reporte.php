<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Reporte de Asistencias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f9;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        form {
            margin: 20px 0;
            text-align: center;
        }
        button {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <h1>Generar Reporte de Asistencias</h1>

    <!-- Tabla para mostrar datos -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ingreso</th>
                <th>Salida</th>
                <th>Fecha</th>
                <th>ID Empleado</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Incluye las configuraciones y la clase de conexión
            require_once $_SERVER['DOCUMENT_ROOT'] . '/asistencia/config.php';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/asistencia/models/conexion.php';

            // Instancia de la clase de conexión
            $conexion = new Conexion();
            $conn = $conexion->conectar();

            // Consulta SQL sin filtros
            $sql = "
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

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($result) {
    foreach ($result as $row) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['ingreso']}</td>
            <td>{$row['salida']}</td>
            <td>{$row['fecha']}</td>
            <td>{$row['nombre_completo']}</td>
            <td>{$row['estado']}</td>
        </tr>";
    }
} else {
    echo '<tr><td colspan="6">No se encontraron resultados.</td></tr>';
}
            $conn = null; // Cierra la conexión
            ?>
        </tbody>
    </table>

    <!-- Botón para generar reporte en Excel -->
 
    <form method="GET" action="/asistencia/controllers/reporteController.php">
    <label for="fecha_inicio">Fecha Inicio:</label>
    <input type="date" name="fecha_inicio" id="fecha_inicio" required>

    <label for="fecha_fin">Fecha Fin:</label>
    <input type="date" name="fecha_fin" id="fecha_fin" required>

    <button type="submit" name="option" value="generarReporteExcel">Generar Reporte</button>
</form>
</body>
</html>

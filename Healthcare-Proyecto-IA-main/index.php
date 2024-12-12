<?php
    if (!isset($_COOKIE['PHPSESSID'])) 
    {
        header("Location: login.php");
        exit();
    }
    session_start();
    $user = $_SESSION['user'];
    include_once 'connection.php';
?>

<?php

// Obtener el ID del usuario logueado
$id_usuario = $_SESSION['user_id'];

include_once 'connection.php';

try {
    // Conexión a la base de datos
    $db = new PDO('mysql:host=localhost;dbname=proyectoia;charset=utf8', 'root', 'root');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consultar los últimos 5 registros del usuario actual
    $query = "SELECT heart_rate, temperature, spo2, timestamp , estado
              FROM health_data 
              WHERE id_paciente = :id_usuario 
              ORDER BY timestamp DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

?>

<?php

// Obtener el ID del usuario logueado
$id_usuario = $_SESSION['user_id'];
$nombre_usuario = $_SESSION['user'];

include_once 'connection.php';
include_once "prolog.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['opciones'])) {
    $estado = $_POST['opciones'];
    actualizarEstadoPaciente($id_usuario, $nombre_usuario, $estado);
}

try {
    

    $estado_res = obtenerEstadoPaciente($id_usuario, $nombre_usuario); // Estado fijo "Dormido" para pruebas

    $estado = isset($estado_res['Error']) ? 'normal' : $estado_res['estado'];

    if (isset($estado_res['Error'])) {
        actualizarEstadoPaciente($id_usuario, $nombre_usuario, $estado);
    }
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 Sensor Data</title>
    <link rel="stylesheet" href="css/index.css">
    <script>
        function fetchData() {
            fetch("<?php echo $_SERVER['PHP_SELF']; ?>")
                .then(response => response.text())
                .then(html => {
                    document.body.innerHTML = html;
                })
                .catch(error => console.error('Error fetching data:', error));
        }
        setInterval(fetchData, 5000); // Actualiza cada 5 segundos
    </script>

</head>
<body>
    <header>
        <?php include("header.php"); ?>
        <div style="clear: both;"></div>
        <hr>
        <?php include("navbar.php"); ?>
        <div style="clear: both;"></div>
    </header>

    <h2>Datos recientes del usuario</h2>
    <table>
    <thead>
        <tr>
            <th>BPM (Ritmo Cardiaco)</th>
            <th>Temperatura (°C)</th>
            <th>Oxigenación (%)</th>
            <th>Fecha y hora</th>
            <th>Estado</th>
            <th>Visualización</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($data)): ?>
            <?php foreach ($data as $row): ?>
                <?php
                // Calcular el porcentaje de la barra
                $minBPM = 60; // Valor mínimo para la escala
                $maxBPM = 200; // Valor máximo para la escala
                $bpm = (int) $row['heart_rate'];
                $percentage = (($bpm - $minBPM) / ($maxBPM - $minBPM)) * 100;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['heart_rate']); ?></td>
                    <td><?php echo htmlspecialchars($row['temperature']); ?></td>
                    <td><?php echo htmlspecialchars($row['spo2']); ?></td>
                    <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                    <td><?php echo htmlspecialchars($row['estado']); ?></td>
                    <td>
                        <div class="bar-container">
                            <div class="bar" style="width: <?php echo max(0, min(100, $percentage)); ?>%;"></div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No hay datos registrados.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

    <form method="POST">
        <label for="opciones">Estado actual:</label>
        <select id="opciones" name="opciones" onchange="this.form.submit()">
            <option value="normal" <?= $estado === 'normal' ? 'selected' : '' ?>>Normal</option>
            <option value="dormido" <?= $estado === 'dormido' ? 'selected' : '' ?>>Dormido</option>
            <option value="reposo" <?= $estado === 'reposo' ? 'selected' : '' ?>>Reposo</option>
            <option value="ejercicio" <?= $estado === 'ejercicio' ? 'selected' : '' ?>>Ejercicio</option>
        </select>
    </form>
</body>
</html>
<?php
session_start();
include_once "prolog.php";

$sesion = sesionActiva();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar que los campos requeridos estén presentes
    $frec_max_normal = $_POST['frec_max_normal'] ?? null;
    $frec_min_normal = $_POST['frec_min_normal'] ?? null;
    $frec_max_reposo = $_POST['frec_max_reposo'] ?? null;
    $frec_min_reposo = $_POST['frec_min_reposo'] ?? null;
    $frec_max_dormido = $_POST['frec_max_dormido'] ?? null;
    $frec_min_dormido = $_POST['frec_min_dormido'] ?? null;
    $frec_max_ejercicio = $_POST['frec_max_ejercicio'] ?? null;
    $frec_min_ejercicio = $_POST['frec_min_ejercicio'] ?? null;

    // Validar que los campos obligatorios estén completos
    if (empty($frec_max_normal) || empty($frec_min_normal)) {
        setcookie('msj_registro', 'Por favor, completa los campos obligatorios de Normal.', time() + 2, "/");
        header("Location: register.php");
        exit();
    }

    $usuario = $sesion['id'];
    $nombre = $sesion['nombre'];
    $fechaHoraActual = date("Y-m-d H:i:s");

    try {
        include 'connection.php';
        // Preparar consulta base
        $query = "INSERT INTO health_data (heart_rate, temperature, timestamp, spo2, id_paciente, estado) VALUES (:heart_rate, :temperature, :timestamp, :spo2, :id_paciente, :estado)";
        $stmt = $connection->prepare($query);

        // Función para ejecutar la inserción si los valores están presentes
        function insertarDatos($stmt, $heart_rate, $usuario, $fechaHoraActual, $estado)
        {
            if ($heart_rate !== null && $heart_rate !== '') {
                $stmt->execute([
                    ':heart_rate' => $heart_rate,
                    ':temperature' => 37,
                    ':timestamp' => $fechaHoraActual,
                    ':spo2' => 99,
                    ':id_paciente' => $usuario,
                    ':estado' => $estado
                ]);
            }
        }

        function actualizarDatosProlog($stmt, $usuario, $nombre, $estado, $frec_min, $frec_max)
        {
            if (($frec_min !== null && $frec_min !== '') || ($frec_max !== null && $frec_max !== '')) {
                $fechaHoraActual = date("Y-m-d H:i:s");
                actualizarRangoRitmoCardiaco($usuario, $nombre, $estado, $frec_min, $frec_max);
                $stmt->bindParam(':id', $usuario);
                $stmt->bindParam(':estado', $estado);
                $stmt->bindParam(':minFreq', $frec_min);
                $stmt->bindParam(':maxFreq', $frec_max);
                $stmt->bindParam(':fechaHoraActual', $fechaHoraActual);
                $stmt->execute();
            }
        }

        // Insertar datos según los estados
        insertarDatos($stmt, $frec_max_normal, $usuario, $fechaHoraActual, 'normal');
        insertarDatos($stmt, $frec_min_normal, $usuario, $fechaHoraActual, 'normal');
        insertarDatos($stmt, $frec_max_reposo, $usuario, $fechaHoraActual, 'reposo');
        insertarDatos($stmt, $frec_min_reposo, $usuario, $fechaHoraActual, 'reposo');
        insertarDatos($stmt, $frec_max_dormido, $usuario, $fechaHoraActual, 'dormido');
        insertarDatos($stmt, $frec_min_dormido, $usuario, $fechaHoraActual, 'dormido');
        insertarDatos($stmt, $frec_max_ejercicio, $usuario, $fechaHoraActual, 'ejercicio');
        insertarDatos($stmt, $frec_min_ejercicio, $usuario, $fechaHoraActual, 'ejercicio');

        $user_exist = "INSERT INTO normal_ranges (id_paciente, estado, heart_rate_min, heart_rate_max, crtd_on) VALUES (:id, :estado, :minFreq, :maxFreq, :fechaHoraActual)";
        $stmt = $connection->prepare($user_exist);
        actualizarDatosProlog($stmt, $usuario, $nombre, 'normal', $frec_min_normal, $frec_max_normal);
        actualizarDatosProlog($stmt, $usuario, $nombre, 'reposo', $reposo, $reposo);
        actualizarDatosProlog($stmt, $usuario, $nombre, 'dormido', $frec_min_dormido, $frec_max_dormido);
        actualizarDatosProlog($stmt, $usuario, $nombre, 'ejercicio', $frec_min_ejercicio, $frec_max_ejercicio);

        setcookie('msj_registro', 'Los datos se han registrado correctamente.', time() + 2, "/");
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error al registrar: " . $e->getMessage());
        setcookie('msj_registro', 'Ocurrió un error. Inténtalo de nuevo.', time() + 2, "/");
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 Sensor Data</title>
    <link rel="stylesheet" href="css/form.css">

</head>

<body>
    <header>
        <?php include("header.php"); ?>
        <div style="clear: both;"></div>
        <hr>
        <?php include("navbar.php"); ?>
        <div style="clear: both;"></div>
    </header>
    <main>
        <div class="contenedor">
            <h2>Registro de Rangos </h2>
            <form method="post">

                <div class="campo">
                    <label for="usuario">Nomal:</label>
                    <div class="inputs-container">
                        <input type="text" name="frec_min_normal" placeholder="Frecc. Min" class="input" require>
                        <input type="text" name="frec_max_normal" placeholder="Frecc. Max" class="input" require>
                    </div>
                </div>

                
                <div class="campo">
                    <label for="usuario">Reposo:</label>
                    <div class="inputs-container">
                        <input type="text" name="frec_min_reposo" placeholder="Frecc. Min" class="input" >
                        <input type="text" name="frec_max_reposo" placeholder="Frecc. Max" class="input" >
                    </div>
                </div>

                <div class="campo">
                    <label for="usuario">Ejercicio:</label>
                    <div class="inputs-container">
                        <input type="text" name="frec_min_ejercicio" placeholder="Frecc. Min" class="input" >
                        <input type="text" name="frec_max_ejercicio" placeholder="Frecc. Max" class="input" >
                    </div>
                </div>

                
                <div class="campo">
                    <label for="usuario">Dormido:</label>
                    <div class="inputs-container">
                        <input type="text" name="frec_min_dormido" placeholder="Frecc. Min" class="input" >
                        <input type="text" name="frec_min_dormido" placeholder="Frecc. Max" class="input" >
                    </div>
                </div>
<!-- 
                <h4>Nomal</h4>
                <div class="datos">
                    <input name="frec_min_normal" placeholder="Frecc. Min" require>
                    <input name="frec_max_normal" placeholder="Frecc. Max" require>
                </div>


                <h4>Reposo</h4>
                <div class="datos">
                    <input name="frec_min_reposo" placeholder="Frecc. Min">
                    <input name="frec_max_reposo" placeholder="Frecc. Max">
                </div>

                <h4>Ejercicio</h4>
                <div class="datos">
                    <input name="frec_min_ejercicio" placeholder="Frecc. Min">
                    <input name="frec_max_ejercicio" placeholder="Frecc. Max">
                </div>


                <h4>Dormido</h4>
                <div class="datos">
                    <input name="frec_min_dormido" placeholder="Frecc. Min">
                    <input name="frec_max_dormido" placeholder="Frecc. Max">
                </div> -->

<!-- 
                <button type="submit">Registrar datos</button> -->


                <div class="botones">
                <button type="submit"  class="btn">Registrar datos</button>
                </div>

            </form>

        </div>



    </main>

</body>

</html>
<!-- <style>
    .datos {
        display: flex;
        flex-direction: row;
        gap: 10px;
        padding: 10px 0px 10px 0px;
    }


    .main {
        display: flex;

        align-items: center;
        justify-content: center;
    }
</style> -->
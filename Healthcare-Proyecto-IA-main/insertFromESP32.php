<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");
include_once "prolog.php";

session_start(); // Iniciar sesión

$sesion = sesionActiva();

if (isset($sesion['Error'])) {
    echo json_encode([
        "status" => "error",
        "message" => $sesion['Error']  // Muestra el mensaje de error
    ]);
    exit();
}

try {
    // Conexión a la base de datos
    $conexion = new PDO('mysql:host=localhost; dbname=proyectoia', 'root', 'root');
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Leer datos enviados por ESP32
    $json = file_get_contents('php://input');
    error_log("Received JSON: " . $json); // Para depuración
    $data = json_decode($json);

    // Validar los campos recibidos
    if (
        isset($data->heart_rate, $data->temperature, $data->spo2) &&
        is_numeric($data->heart_rate) &&
        is_numeric($data->temperature) &&
        is_numeric($data->spo2)
    ) {

        // Obtener datos
        $heart_rate = $data->heart_rate;
        $temperature = $data->temperature;
        $spo2 = $data->spo2;
        $id_paciente = $sesion['id']; // ID del usuario en sesión
        $nombre = $sesion['nombre'];
        $estado_res = obtenerEstadoPaciente($id_paciente, $nombre); // Estado fijo "Dormido" para pruebas

        $estado = isset($estado_res['Error']) ? 'normal' : $estado_res['estado'];

        if (isset($estado_res['Error'])) {
            actualizarEstadoPaciente($id_paciente, $nombre, $estado);
        }

        actualizarSignosVitales($id_paciente, $nombre, $heart_rate, $spo2, $temperature);

        $sql = "SELECT Tel FROM usuarios WHERE ID_usuario = :id_paciente";
        $stm = $conexion->prepare($sql);
        
        // Vincula el parámetro de la consulta
        $stm->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
        
        // Ejecuta la consulta
        $stm->execute();
        
        // Obtén el resultado
        $tel = $stm->fetchColumn();

        $alertas_activas = obtenerAlertas($id_paciente, $nombre, $heart_rate, $spo2, $temperature);
        mandarAlerta($alertas_activas, $nombre, $estado, $heart_rate, $spo2, $temperature, $tel);

        // Consulta SQL para insertar los datos
        $sql = "INSERT INTO health_data (heart_rate, temperature, spo2, id_paciente, estado) 
                VALUES (:heart_rate, :temperature, :spo2, :id_paciente, :estado)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':heart_rate', $heart_rate);
        $stmt->bindParam(':temperature', $temperature);
        $stmt->bindParam(':spo2', $spo2);
        $stmt->bindParam(':id_paciente', $id_paciente);
        $stmt->bindParam(':estado', $estado);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Data saved"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save data"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid or missing fields"]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage()); // Log de errores
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
}

function mandarAlerta($alertas, $paciente, $estado, $frecuencia, $oxigenacion, $temperatura, $tel)
{
    if ($alertas[0] != "No se activó ninguna alarma.") {
        //se envia el numero del cel del paciente (implemnetarlo en el registro) y el mensaje de la alerta
        //logica de alerta
        $mensaje = "Alerta. El paciente $paciente presenta riesgos de salud.\nLos signos vitales que presenta son: \nEstado: $estado \nFrecuencia cardiaca: $frecuencia \nOxigenación: $oxigenacion \nTemperatura: $temperatura\nSe activaron las siguientes alertas: \n";
        for ($i = 0; $i < count($alertas) - 1; $i++) {
            $mensaje .= "{$alertas[$i]}\n";
        }

        EnviarDatos($tel, $mensaje);
    }
}



function EnviarDatos($number, $message)
{
    // URL del servidor al que deseas enviar la petición
    $url = "http://localhost:8000/alerta";

    // Datos que deseas enviar en la petición POST
    $data = array(
        "number" => "$number",
        "message" => "$message"
    );

    // Convertir los datos en formato JSON
    $jsonData = json_encode($data);

    // Configurar cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    // Ejecutar la petición y obtener la respuesta
    $response = curl_exec($ch);

    // Manejo de errores
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    } else {
        // Mostrar la respuesta del servidor
        echo 'Respuesta del servidor: ' . $response;
    }

    // Cerrar la sesión cURL
    curl_close($ch);
}

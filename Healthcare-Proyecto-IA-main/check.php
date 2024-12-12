<?php
session_start(); // Asegurarse de iniciar la sesión al inicio del archivo
include_once "prolog.php";

// Verificar que los datos fueron enviados
if (isset($_POST['usuario']) && isset($_POST['contrasena'])) {
    $usuario = trim($_POST['usuario']); // Eliminar espacios en blanco
    $contrasena = trim($_POST['contrasena']);

    // Validar que no estén vacíos
    if (empty($usuario) || empty($contrasena)) {
        setcookie('mensaje', 'Por favor, completa todos los campos.', time() + 5, "/");
        header("Location: login.php");
        exit();
    }

    // Conexión a la base de datos
    include 'connection.php';

    try {
        // Preparar consulta segura
        $user_exist = "SELECT * FROM usuarios WHERE Usuario = :usuario AND Contrasena = :contrasena";
        $stmt = $connection->prepare($user_exist);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':contrasena', $contrasena);
        $stmt->execute();
        $response = $stmt->fetch(PDO::FETCH_ASSOC); // Usar fetch para obtener un solo registro

        if ($response) {
            // Validar si el usuario está deshabilitado
            if ($response['Estatus'] == 0) {
                setcookie('mensaje', 'Tu cuenta está deshabilitada. Por favor, contacta al administrador.', time() + 5, "/");
                header("Location: login.php");
                exit();
            }
            // Iniciar sesión
            $_SESSION['user'] = $response['Nombre']; // Nombre del usuario
            $_SESSION['user_id'] = $response['ID_Usuario']; // ID del usuario
            $_SESSION['access'] = ($response['ID_Usuario'] == 1) ? "admin" : "user"; // Rol del usuario
            $id = $response['ID_Usuario'];
            $nombre = $response['Nombre'];
    // echo("<script>console.log('PHP: " . $data . "');</script>");
    $data = ejecutarProlog("iniciar_sesion($id, '$nombre')");
    // Log para confirmar la sesión
    //error_log("Sesión iniciada: " . print_r($_SESSION, true));

    echo json_encode(['success' => true, 'prolog_response' => $data]);

            // Validar si el usuario tiene datos registrados
            $validacion = ValidarUser($id, $nombre);

            if (isset($validacion['error'])) {
                setcookie('mensaje', $validacion['error'], time() + 5, "/");
                header("Location: Form.php");
                exit();
            }

            if ($validacion['estado'] === "error") {
                // Redirigir al formulario
                header("Location: Form.php");
                exit();
            } else {
                // Redirigir al índice
                header("Location: index.php");
                exit();
            }
        } else {
            // Usuario no encontrado o contraseña incorrecta
            setcookie('mensaje', 'No existe el usuario o la contraseña es incorrecta.', time() + 5, "/");
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        // Error en la base de datos
        error_log("Database error: " . $e->getMessage());
        setcookie('mensaje', 'Ocurrió un error al intentar iniciar sesión. Inténtalo de nuevo.', time() + 5, "/");
        header("Location: login.php");
        exit();
    }
} else {
    // Datos no enviados correctamente
    setcookie('mensaje', 'Por favor, completa todos los campos.', time() + 5, "/");
    header("Location: login.php");
    exit();
}

function ValidarUser($id, $nombre)
{
    $ranges = normalRangesByState($id, null, null, null, 'normal_ranges');
    $health_data = normalRangesByState($id, null, null, null, 'health_data');

    if (empty($ranges) && !empty($health_data)) {
        // El inicio de sesión está bien, ahora enviamos la solicitud al endpoint
        $url_base = "http://localhost:8000/rangos_normales"; // URL del endpoint
        $id_paciente = $_SESSION['user_id']; // ID del usuario logueado
        $url = $url_base . "?id_paciente=" . $id_paciente;

        // Realizar la solicitud GET
        $response = @file_get_contents($url);

        // Manejo de la respuesta
        if ($response === false) {
            return ["error" => "No se pudo conectar al servidor"];
        }

        // Decodificar la respuesta JSON
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ["error" => "Error al decodificar JSON: " . json_last_error_msg()];
        }

        // Verificar si el JSON tiene el formato esperado
        if (!isset($data['status']) || $data['status'] !== "success" || !isset($data['data']['ranges'])) {
            return ["error" => "Formato de respuesta inválido"];
        }

        // Recorrer los rangos para verificar si todos contienen errores
        $rangos = $data['data']['ranges'];
        $todosConError = true;

        foreach ($rangos as $rango) {
            foreach ($rango as $estado => $detalle) {
                if (!isset($detalle['error'])) {
                    $todosConError = false;
                    list($minFreq, $maxFreq) = explode(' - ', $detalle['heart_rate']);
                    actualizarRangoRitmoCardiaco($id, $nombre, $estado, $minFreq, $maxFreq);
                    normalRangesByState($id, $estado, $maxFreq, $minFreq, 'insertar');
                }
            }
        }

        // Si todos los rangos tienen errores, retornar como error
        if ($todosConError) {
            return ["estado" => "error", "mensaje" => "No hay datos registrados para este usuario."];
        }

        // Si hay datos válidos, retornar los rangos registrados
        return ["estado" => "ok", "data" => $data];
    } elseif (!empty($ranges)) {
        foreach ($ranges as $rango) {
            $estado = $rango['estado'];
            $minFreq = $rango['heart_rate_min'];
            $maxFreq = $rango['heart_rate_max'];
            actualizarRangoRitmoCardiaco($id, $nombre, $estado, $minFreq, $maxFreq);
        }
        header("Location: index.php");
        exit();
    } else {
        header("Location: form.php");
    }

    return ["error" => "Bandera en falso. No se realizó la validación."];
}

function normalRangesByState($id, $estado, $maxFreq, $minFreq, $type) {
    include 'connection.php';

    // when type is true, select
    if ($type == 'normal_ranges') {    
        $fechaHoraActual = date("Y-m-d");
        $user_exist = "SELECT * FROM normal_ranges WHERE id_paciente = :id AND DATE(crtd_on) = :date";
        $stmt = $connection->prepare($user_exist);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':date', $fechaHoraActual);
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response;
    } elseif($type == 'insertar') { // when type is false, insert
        $fechaHoraActual = date("Y-m-d H:i:s");
        $user_exist = "INSERT INTO normal_ranges (id_paciente, estado, heart_rate_min, heart_rate_max, crtd_on) VALUES (:id, :estado, :minFreq, :maxFreq, :fechaHoraActual)";
        $stmt = $connection->prepare($user_exist);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':minFreq', $minFreq);
        $stmt->bindParam(':maxFreq', $maxFreq);
        $stmt->bindParam(':fechaHoraActual', $fechaHoraActual);
        $stmt->execute();

        return true;
    } elseif ($type == 'health_data') {
        $user_exist = "SELECT * FROM `health_data` WHERE id_paciente = :id LIMIT 1";
        $stmt = $connection->prepare($user_exist);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response;
    }
}
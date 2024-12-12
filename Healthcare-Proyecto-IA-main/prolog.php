<?php
//header("Content-Type: application/json");

function ejecutarProlog($consulta)
{
    //$swiplPath = "C:/Program Files/swipl/bin/swipl.exe"; // Ruta completa al ejecutable
    //$archivoProlog = "C:/xampp/htdocs/Healthcare-Proyecto-IA/conocimiento.pl";
    $archivoProlog = "C:/wamp64/www/gabs/PROYECTOIA/Healthcare-Proyecto-IA/conocimiento.pl"; // Ruta al archivo Prolog
    $comando = "swipl -s \"$archivoProlog\" -g \"$consulta, halt.\"";

    // Ejecutar el comando
    exec($comando, $salida, $codigoSalida);

    // Verificar si hubo errores
    if ($codigoSalida !== 0) {
        return ["error" => "Error al ejecutar Prolog", "codigo" => $codigoSalida];
    }

    //return ["resultado" => $salida];
    // Devolver información detallada para diagnóstico
    return $salida;
}

function formatearSalida($data)
{
    $partes = explode(", ", $data);

    // Extrae los valores de cada parte
    $data = [];
    foreach ($partes as $parte) {
        list($clave, $valor) = explode(": ", $parte);
        $data[trim($clave)] = is_numeric($valor) ? (strpos($valor, '.') !== false ? (float) $valor : (int) $valor) : $valor;
    }

    return $data;
}

// Función para actualizar signos vitales
function actualizarSignosVitales($id, $nombre, $frecuencia, $oxigenacion, $temperatura)
{
    $consulta = "actualizar_signos_vitales($id, '$nombre', $frecuencia, $oxigenacion, $temperatura)";
    return ejecutarProlog($consulta);
}

// Función para actualizar estado del paciente
function actualizarEstadoPaciente($id, $nombre, $estado)
{
    $consulta = "actualizar_estado_paciente($id, '$nombre', $estado)";
    return ejecutarProlog($consulta);
}

// Función para actualizar rango de ritmo cardíaco
function actualizarRangoRitmoCardiaco($id, $nombre, $estado, $minFreq, $maxFreq)
{
    $consulta = "actualizar_rango_ritmo_cardiaco($id, '$nombre', $estado, $minFreq, $maxFreq)";
    return ejecutarProlog($consulta);
}

// Función para actualizar enfermedad del paciente
function agregarEnfermedadPaciente($id, $nombre, $problema)
{
    $consulta = "agregar_enfermedad_paciente($id, '$nombre', $problema)";
    return ejecutarProlog($consulta);
}

function borrarEnfermedadPaciente($id, $nombre, $problema)
{
    $consulta = "borrar_enfermedad_paciente($id, '$nombre', $problema)";
    return ejecutarProlog($consulta);
}

function obtenerSignosVitales($id, $nombre)
{
    $consulta = "obtener_signos_vitales($id, '$nombre', Frecuencia, Oxigenacion, Temperatura)";
    $result = ejecutarProlog($consulta);

    return formatearSalida($result[0]);
}

function obtenerRangoOximetria()
{
    $consulta = "obtener_rango_oximetria(ValorMinimo)";
    $result = ejecutarProlog($consulta);

    return formatearSalida($result[0]);
}

function obtenerRangoTemperatura()
{
    $consulta = "obtener_rango_temperatura(MinTemp, MaxTemp)";
    $result = ejecutarProlog($consulta);

    return formatearSalida($result[0]);
}

function obtenerEstadoPaciente($id, $nombre)
{
    $consulta = "obtener_estado_paciente($id, '$nombre')";
    $result = ejecutarProlog($consulta);

    return formatearSalida($result[0]);
}

function obtenerEnfermedadesPacientes($id, $nombre)
{
    $consulta = "obtener_enfermedades_paciente($id, '$nombre', Problemas)";
    $result = ejecutarProlog($consulta);

    $rawData = $result[0];

    $formattedData = str_replace(["[", "]"], "", $rawData);
    $enfermedades = explode(",", $formattedData);
    $data = array_map('trim', $enfermedades);
    if (str_contains($data[0], "Error:")) {
        return formatearSalida($data[0]);
    }

    return $data;
}

function obtenerRangosRitmoCardiaco($id, $nombre)
{
    $consulta = "obtener_rangos_ritmo_cardiaco($id, '$nombre', Resultados)";
    $resultado = ejecutarProlog($consulta);

    $data = $resultado[0];

    // Remplaza las comillas simples y convierte la cadena en un array PHP
    $data = str_replace("'", '"', $data);
    $data = preg_replace('/([a-zA-Z]+)/', '"$1"', $data); // Agregar comillas a los valores de texto
    $array = json_decode($data, true);

    // Formatea los datos como un array de objetos JSON
    $formatted = [];
    if ($array) {
        foreach ($array as $item) {
            // Crea un array asociativo para cada elemento
            $json_item = [
                "estado" => $item[0],
                "min" => $item[1],
                "max" => $item[2]
            ];

            // Agrega el objeto JSON al array final
            $formatted[] = $json_item;
        }
    } else {
        return formatearSalida($resultado[0]);
    }

    // Convierte el array final a un JSON completo
    return $formatted;

}

function iniciarSesion($id, $nombre)
{
    $consulta = "iniciar_sesion($id, '$nombre')";
    $result = ejecutarProlog($consulta);

    return formatearSalida($result[0]);
}

function cerrarSesion()
{
    $consulta = "cerrar_sesion";
    $result = ejecutarProlog($consulta);

    return formatearSalida($result[0]);
}

function sesionActiva()
{
    $consulta = "sesion_activa";
    $result = ejecutarProlog($consulta);

    return formatearSalida($result[0]);
}

function obtenerAlertas($id, $nombre, $frecuencia, $oxigenacion, $temperatura)
{
    $consulta = "comprobar_alarmas($id, '$nombre', $frecuencia, $oxigenacion, $temperatura, Alarmas)";
    $result = ejecutarProlog($consulta);

    return $result;
}

// Función para procesar las solicitudes
function procesarSolicitud($data)
{
    if (!isset($data['tipo'])) {
        return ["error" => "El campo 'tipo' es obligatorio."];
    }

    $tipo = $data['tipo'];

    switch ($tipo) {
        case 'actualizar_signos_vitales':
            if (isset($data['id'], $data['nombre'], $data['frecuencia'], $data['oxigenacion'], $data['temperatura'])) {
                $consulta = actualizarSignosVitales($data['id'], $data['nombre'], $data['frecuencia'], $data['oxigenacion'], $data['temperatura']);
                return $consulta;
            } else {
                return ["error" => "Faltan parámetros para 'actualizar_signos_vitales'."];
            }

        case 'actualizar_estado_paciente':
            if (isset($data['id'], $data['nombre'], $data['estado'])) {
                $consulta = actualizarEstadoPaciente($data['id'], $data['nombre'], $data['estado']);
                return $consulta;
            } else {
                return ["error" => "Faltan parámetros para 'actualizar_estado_paciente'."];
            }

        case 'actualizar_rango_ritmo_cardiaco':
            if (isset($data['id'], $data['nombre'], $data['estado'], $data['minFreq'], $data['maxFreq'])) {
                $consulta = actualizarRangoRitmoCardiaco($data['id'], $data['nombre'], $data['estado'], $data['minFreq'], $data['maxFreq']);
                return $consulta;
            } else {
                return ["error" => "Faltan parámetros para 'actualizar_rango_ritmo_cardiaco'."];
            }

        case 'agregar_enfermedad_paciente':
            if (isset($data['id'], $data['nombre'], $data['problema'])) {
                $consulta = agregarEnfermedadPaciente($data['id'], $data['nombre'], $data['problema']);
                return $consulta;
            } else {
                return ["error" => "Faltan parámetros para 'agregar_enfermedad_paciente'."];
            }
        case 'borrar_enfermedad_paciente':
            if (isset($data['id'], $data['nombre'], $data['problema'])) {
                $consulta = borrarEnfermedadPaciente($data['id'], $data['nombre'], $data['problema']);
                return $consulta;
            } else {
                return ["error" => "Faltan parámetros para 'borrar_enfermedad_paciente'."];
            }
        case 'obtener_signos_vitales':
            if (isset($data['id'], $data['nombre'])) {
                $consulta = obtenerSignosVitales($data['id'], $data['nombre']);
                return $consulta;
            } else {
                return ["error" => "Faltan parámetros para 'obtener_signos_vitales'."];
            }
        case 'obtener_rangos_ritmo_cardiaco':
            if (isset($data['id'], $data['nombre'])) {
                $consulta = obtenerRangosRitmoCardiaco($data['id'], $data['nombre']);
                return $consulta;
            } else {
                return ["error" => "Faltan parámetros para 'obtener_rangos_ritmo_cardiaco'."];
            }
        case 'obtener_estado_paciente':
            if (isset($data['id'], $data['nombre'])) {
                $consulta = obtenerEstadoPaciente($data['id'], $data['nombre']);
                return $consulta;
            } else {
                return ["error" => "Faltan parámetros para 'obtener_estado_paciente'."];
            }
        case 'obtener_enfermedades_paciente':
            if (isset($data['id'], $data['nombre'])) {
                $consulta = obtenerEnfermedadesPacientes($data['id'], $data['nombre']);
                return $consulta;
            } else {
                return ["error" => "Faltan parámetros para 'obtener_enfermedades_paciente'."];
            }
        case 'obtener_rango_oximetria':
            $consulta = obtenerRangoOximetria();
            return $consulta;
        case 'obtener_rango_temperatura':
            $consulta = obtenerRangoTemperatura();
            return $consulta;
        case 'iniciar_sesion':
            if (isset($data['id'], $data['nombre'])) {
                $consulta = iniciarSesion($data['id'], $data['nombre']);
                return $consulta;
            } else {
                return ["error" => "Faltan parámetros para 'iniciar_sesion'."];
            }
        case 'cerrar_sesion':
            $consulta = cerrarSesion();
            return $consulta;
        case 'sesion_activa':
            $consulta = sesionActiva();
            return $consulta;
        case 'obtener_alertas':
            if (isset($data['id'], $data['nombre'], $data['frecuencia'], $data['oxigenacion'], $data['temperatura'])) {
                $consulta = obtenerAlertas($data['id'], $data['nombre'], $data['frecuencia'], $data['oxigenacion'], $data['temperatura']);
                return $consulta;
            } else {
                return ["error" => "Faltan parámetros para 'iniciar_sesion'."];
            }
        default:
            return ["error" => "El tipo especificado no es válido."];
    }
}

/* if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer datos de entrada (JSON)
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // Validar datos de entrada
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["error" => "Formato JSON inválido."]);
        exit;
    }

    // Procesar solicitud
    $response = procesarSolicitud($data);

    // Responder
    echo json_encode($response, JSON_PRETTY_PRINT);
} else {
    echo "Método no permitido.";
} */
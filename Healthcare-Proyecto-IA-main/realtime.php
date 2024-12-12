<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

// Conexión a la base de datos
try {
    $conexion = new PDO('mysql:host=localhost; dbname=proyectoia', 'root', 'root');
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener los últimos 10 registros
    $sql = "SELECT id, heart_rate, temperature, spo2, timestamp  FROM health_data ORDER BY id DESC LIMIT 10";
    $stmt = $conexion->query($sql);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $data]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
}

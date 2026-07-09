<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (
    empty($data->name) ||
    empty($data->email) ||
    empty($data->password) ||
    empty($data->phone)
) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. Required: name, email, password, phone"]);
    exit();
}

try {
    // Cek apakah email sudah terdaftar
    $check_query = "SELECT id FROM users WHERE email = :email LIMIT 1";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(":email", $data->email);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Email sudah terdaftar!"]);
        exit();
    }

    // Insert user baru
    $query = "INSERT INTO users (name, email, password, phone) VALUES (:name, :email, :password, :phone)";
    $stmt = $conn->prepare($query);

    // Hash password demi keamanan
    $hashed_password = password_hash($data->password, PASSWORD_BCRYPT);

    $stmt->bindParam(":name", $data->name);
    $stmt->bindParam(":email", $data->email);
    $stmt->bindParam(":password", $hashed_password);
    $stmt->bindParam(":phone", $data->phone);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Pendaftaran berhasil!"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Gagal mendaftarkan user."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}
?>

<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->email) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. Required: email, password"]);
    exit();
}

try {
    $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":email", $data->email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verifikasi password
        if (password_verify($data->password, $row['password'])) {
            // Hapus password dari data response demi keamanan
            unset($row['password']);
            
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Login berhasil!",
                "data" => $row
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Password salah!"]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Email tidak terdaftar!"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}
?>

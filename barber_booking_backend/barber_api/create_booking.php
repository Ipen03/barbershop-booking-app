<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (
    empty($data->user_id) ||
    empty($data->service_id) ||
    empty($data->barber_id) ||
    empty($data->booking_date) ||
    empty($data->booking_time)
) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. Required: user_id, service_id, barber_id, booking_date, booking_time"]);
    exit();
}

try {
    $query = "INSERT INTO bookings (user_id, service_id, barber_id, booking_date, booking_time, notes, status) 
              VALUES (:user_id, :service_id, :barber_id, :booking_date, :booking_time, :notes, 'Pending')";
    
    $stmt = $conn->prepare($query);

    $notes = isset($data->notes) ? $data->notes : "";

    $stmt->bindParam(":user_id", $data->user_id);
    $stmt->bindParam(":service_id", $data->service_id);
    $stmt->bindParam(":barber_id", $data->barber_id);
    $stmt->bindParam(":booking_date", $data->booking_date);
    $stmt->bindParam(":booking_time", $data->booking_time);
    $stmt->bindParam(":notes", $notes);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Booking berhasil dibuat!",
            "booking_id" => $conn->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Gagal membuat booking."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}
?>

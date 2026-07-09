<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

if (empty($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing parameter: user_id"]);
    exit();
}

$user_id = $_GET['user_id'];

try {
    $query = "SELECT b.id, b.booking_date, b.booking_time, b.status, b.notes, b.created_at,
                     s.name as service_name, s.price as service_price, s.duration as service_duration, 
                     ba.name as barber_name, ba.rating as barber_rating 
              FROM bookings b
              JOIN services s ON b.service_id = s.id
              JOIN barbers ba ON b.barber_id = ba.id
              WHERE b.user_id = :user_id
              ORDER BY b.booking_date DESC, b.booking_time DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => $bookings
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}
?>

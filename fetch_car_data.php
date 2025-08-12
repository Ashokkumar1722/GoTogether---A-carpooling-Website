<?php
include 'includes/db.php';

$type = $_GET['type'] ?? '';

if ($type === 'year') {
    if (isset($_GET['car_make']) && $_GET['car_make'] !== '') {
        $car_make = $_GET['car_make'];
        $stmt = $conn->prepare("SELECT DISTINCT car_year FROM car_make WHERE car_make = ? ORDER BY car_year DESC");
        $stmt->bind_param("s", $car_make);
    } else {
        $stmt = $conn->prepare("SELECT DISTINCT car_year FROM car_make ORDER BY car_year DESC");
    }

    if (!$stmt) {
        echo json_encode(["error" => "SQL prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["error" => "No data found"]);
        exit;
    }

    $years = [];
    while ($row = $result->fetch_assoc()) {
        $years[] = $row;
    }

    echo json_encode($years);
    exit;
}
?>
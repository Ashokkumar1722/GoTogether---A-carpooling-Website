<?php
include 'includes/db.php';

if (isset($_GET['manufacturer'])) {
    $manufacturer = mysqli_real_escape_string($conn, $_GET['manufacturer']);
    $query = "SELECT DISTINCT model FROM car_make WHERE manufacturer = '$manufacturer' ORDER BY model";
    
    $result = mysqli_query($conn, $query);
    $models = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $models[] = $row['model'];
    }

    echo json_encode($models);
}
?>
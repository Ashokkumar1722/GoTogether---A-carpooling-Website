<?php
include 'includes/db.php';

if (isset($_GET['manufacturer']) && isset($_GET['model'])) {
    $manufacturer = $_GET['manufacturer'];
    $model = $_GET['model'];

    $query = "SELECT fuel_type FROM car_make WHERE manufacturer = '$manufacturer' AND model = '$model' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        echo $row['fuel_type'];
    } else {
        echo 'Unknown';
    }
}
?>
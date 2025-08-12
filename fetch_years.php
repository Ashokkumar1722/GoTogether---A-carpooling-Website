<?php
include 'includes/db.php';

$manufacturer = $_GET['manufacturer'];
$model = $_GET['model'];

$response = [
    "years" => [],
    "mileage" => null
];

$query = "SELECT year, mileage FROM car_make WHERE manufacturer = '$manufacturer' AND model = '$model'";
$result = mysqli_query($conn, $query);

$years = [];
$mileage = null;

while ($row = mysqli_fetch_assoc($result)) {
    $years[] = $row['year'];
    $mileage = $row['mileage']; // Assuming all rows have the same mileage
}

$response["years"] = $years;
$response["mileage"] = $mileage;

echo json_encode($response);
?>
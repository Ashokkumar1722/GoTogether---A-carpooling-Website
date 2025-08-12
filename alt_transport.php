<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['from'];
    $to = $_POST['to'];

    // Check for available carpool rides
    $stmt = $conn->prepare("SELECT * FROM rides WHERE route_from = ? AND route_to = ? AND available_seats > 0");
    $stmt->bind_param("ss", $from, $to);
    $stmt->execute();
    $carpool_result = $stmt->get_result();

    if ($carpool_result->num_rows > 0) {
        $available_rides = $carpool_result->fetch_all(MYSQLI_ASSOC);
    } else {
        // If no carpool, fetch alternative transport options
        $stmt = $conn->prepare("SELECT * FROM alternative_transport WHERE from_location = ? AND to_location = ?");
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $alt_transport_result = $stmt->get_result();
        $alternative_rides = $alt_transport_result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Alternative Transport</title>
    <link rel="stylesheet" href="assets/alt_transport.css">
</head>

<body>
    <div class="container">
        <h1>Go<span> Together</span></h1>
        <h2>Find an Alternative Ride</h2>
        <form method="POST">
            <input type="text" name="from" placeholder="Enter From Location" required>
            <input type="text" name="to" placeholder="Enter To Destination" required>
            <button type="submit">Search</button>
        </form>

        <?php if (isset($available_rides)): ?>
        <h3>Available Carpool Rides:</h3>
        <ul>
            <?php foreach ($available_rides as $ride): ?>
            <li><?php echo "From: {$ride['route_from']} - To: {$ride['route_to']} | Seats: {$ride['available_seats']} | Date: {$ride['date']}"; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php elseif (isset($alternative_rides)): ?>
        <h3>No Carpool Found! Alternative Transport Options:</h3>
        <ul>
            <?php foreach ($alternative_rides as $alt): ?>
            <li>
                <?php echo "{$alt['transport_type']} from {$alt['from_location']} to {$alt['to_location']} | Provider: {$alt['provider']} | Departure: {$alt['departure_time']} | Arrival: {$alt['arrival_time']}"; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</body>

</html>
<?php
session_start();
include 'includes/db.php';
require 'includes/mailer.php'; // Include mailer function

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION['email'];

// Handle ride booking
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_ride'])) {
    $pickup = $_POST['pickup'];
    $dropoff = $_POST['dropoff'];
    $pickup_date = $_POST['pickup_date'];
    $pickup_time = $_POST['pickup_time'];
    $dropoff_time = $_POST['dropoff_time'];
    $required_seats = $_POST['required_seats'];

    $stmt = $conn->prepare("SELECT id, available_seats FROM rides WHERE route_from = ? AND route_to = ? AND available_seats >= ? AND status = 'pending' LIMIT 1");
    $stmt->bind_param("ssi", $pickup, $dropoff, $required_seats);
    $stmt->execute();
    $result = $stmt->get_result();
    $ride = $result->fetch_assoc();
    $stmt->close();

    if (!$ride) {
        echo "<script>alert('No available rides found for this route.');</script>";
    } else {
        $ride_id = $ride['id'];

        $stmt = $conn->prepare("INSERT INTO bookings (ride_id, user_email, pickup, dropoff, pickup_date, required_seats, status, pickup_time, dropoff_time) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->bind_param("issssiss", $ride_id, $user_email, $pickup, $dropoff, $pickup_date, $required_seats, $pickup_time, $dropoff_time);

        if ($stmt->execute()) {
            $stmt = $conn->prepare("UPDATE rides SET available_seats = available_seats - ? WHERE id = ?");
            $stmt->bind_param("ii", $required_seats, $ride_id);
            $stmt->execute();
            $stmt->close();

            $subject = "Booking Confirmation - GoTogether";
            $message = "<h3>Your ride booking request has been submitted.</h3>
                        <p>Pickup: $pickup</p>
                        <p>Dropoff: $dropoff</p>
                        <p>Date: $pickup_date</p>
                        <p>Seats: $required_seats</p>
                        <p>Status: Pending Approval</p>
                        <br><p>Thank you for using GoTogether!</p>";
            sendEmail($user_email, $subject, $message);

            echo "<script>alert('Booking successful! Waiting for approval.'); window.location.href='home.php';</script>";
        } else {
            echo "<script>alert('Error booking the ride. Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Book a Ride</title>
    <link rel="stylesheet" href="assets/book_ride.css">
</head>

<body>
    <h1>Book a Ride</h1>
    <form method="POST">
        <input type="text" name="pickup" placeholder="Pickup Location" required>
        <input type="text" name="dropoff" placeholder="Dropoff Location" required>
        <input type="date" name="pickup_date" required>
        <input type="time" name="pickup_time" required>
        <input type="time" name="dropoff_time" required>
        <input type="number" name="required_seats" placeholder="Seats Required" required min="1">

        <button type="submit" name="book_ride">Book Ride</button>
    </form>

    <?php
    // Show available rides based on search
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_rides'])) {
        $pickup = $_POST['pickup'];
        $dropoff = $_POST['dropoff'];
        $required_seats = $_POST['required_seats'];

        $stmt = $conn->prepare("SELECT * FROM rides WHERE route_from = ? AND route_to = ? AND available_seats >= ? AND status = 'pending'");
        $stmt->bind_param("ssi", $pickup, $dropoff, $required_seats);
        $stmt->execute();
        $result = $stmt->get_result();



        if ($result->num_rows > 0) {
            while ($ride = $result->fetch_assoc()) {
                echo "<div class='ride-box'>";
                echo "<p><strong>Driver:</strong> " . htmlspecialchars($ride['owner_email']) . "</p>";
                echo "<p><strong>From:</strong> " . htmlspecialchars($ride['route_from']) . " | <strong>To:</strong> " . htmlspecialchars($ride['route_to']) . "</p>";
                echo "<p><strong>Date:</strong> " . htmlspecialchars($ride['ride_date']) . "</p>";
                echo "<p><strong>Time:</strong> " . htmlspecialchars($ride['ride_time']) . "</p>";
                echo "<p><strong>Available Seats:</strong> " . $ride['available_seats'] . "</p>";
                echo "</div><hr>";
            }
        }
        $stmt->close();
    }
    ?>
</body>

</html>
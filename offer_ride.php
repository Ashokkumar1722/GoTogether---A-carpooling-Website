<?php
session_start();
include 'includes/db.php';
include 'includes/mailer.php'; // Separate mail file

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle ride posting
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_ride'])) {
    $car_model = $_POST['car_model'];
    $car_number = $_POST['car_number'];
    $available_seats = $_POST['available_seats'];
    $route_from = $_POST['route_from'];
    $route_to = $_POST['route_to'];
    $date = date('Y-m-d', strtotime($_POST['date'])); // Fix date format
    $time = date('H:i:s', strtotime($_POST['time'])); // Fix time format

    $stmt = $conn->prepare("INSERT INTO rides (owner_id, car_model, car_number, available_seats, route_from, route_to, date, time) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississss", $user_id, $car_model, $car_number, $available_seats, $route_from, $route_to, $date, $time);

    if ($stmt->execute()) {
        echo "<script>alert('Ride posted successfully!'); window.location.href='offer_ride.php';</script>";
    } else {
        echo "<script>alert('Error posting ride!');</script>";
    }
    $stmt->close();
}

// Fetch all pending ride requests
$sql = "SELECT b.id AS booking_id, b.user_email, b.pickup, b.dropoff, 
               b.required_seats, b.status, 
               DATE_FORMAT(b.pickup_date, '%Y-%m-%d') AS pickup_date,
               TIME_FORMAT(b.pickup_time, '%h:%i %p') AS pickup_time,
               TIME_FORMAT(b.dropoff_time, '%h:%i %p') AS dropoff_time
        FROM bookings b 
        WHERE b.status = 'pending'";

$result = $conn->query($sql);

// Approve or Reject Requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['approve']) || isset($_POST['reject']))) {
    $booking_id = $_POST['booking_id'];
    $user_email = $_POST['user_email'];
    $status = isset($_POST['approve']) ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);

    if ($stmt->execute()) {
        // Email notification
        $subject = "Your ride request has been $status!";
        $message = "<h3>Your ride request has been $status.</h3>
                    <p>Thank you for using GoTogether!</p>";

        sendEmail($user_email, $subject, $message);
        echo "<script>alert('Booking $status successfully!'); window.location.href='offer_ride.php';</script>";
    } else {
        echo "<script>alert('Error updating booking!');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Offer a Ride</title>
    <link rel="stylesheet" href="assets/offer_ride.css">
</head>

<body>
    <h1>GO<span> TOGETHER</span></h1>
    <h2>Post a Ride</h2>
    <form method="POST">
        <input type="text" name="car_model" placeholder="Car Model" required>
        <input type="text" name="car_number" placeholder="Car Number" required>
        <input type="number" name="available_seats" placeholder="Available Seats" required>
        <input type="text" name="route_from" placeholder="From" required>
        <input type="text" name="route_to" placeholder="To" required>
        <input type="date" name="date" required>
        <input type="time" name="time" required>
        <button type="submit" name="post_ride">Post Ride</button>
    </form>

    <h1>Ride Requests</h1>
    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Pickup</th>
            <th>Dropoff</th>
            <th>Pickup Date</th>
            <th>Pickup Time</th>
            <th>Dropoff Time</th>
            <th>Seats</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['pickup']); ?></td>
            <td><?php echo htmlspecialchars($row['dropoff']); ?></td>
            <td><?php echo htmlspecialchars($row['pickup_date']); ?></td>
            <td><?php echo htmlspecialchars($row['pickup_time']); ?></td>
            <td><?php echo htmlspecialchars($row['dropoff_time']); ?></td>
            <td><?php echo htmlspecialchars($row['required_seats']); ?></td>
            <td>
                <form class="bookings" method="POST" action="approve.php">

                    <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($row['user_email']); ?>">
                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($row['booking_id']); ?>">
                    <button type="submit" name="approve">Approve</button>
                    <button type="submit" name="reject">Reject</button>
                </form>
            </td>
        </tr>

        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <p>No pending ride requests.</p>
    <?php endif; ?>
    <h2>Routine Ride Requests</h2>
    <?php
$routine_sql = "SELECT * FROM routine_bookings WHERE status = 'pending'";
$routine_result = $conn->query($routine_sql);

if ($routine_result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Pickup</th>
            <th>Dropoff</th>
            <th>Days</th>
            <th>Pickup Time</th>
            <th>Dropoff Time</th>
            <th>Members</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $routine_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['pickup_location']); ?></td>
            <td><?php echo htmlspecialchars($row['dropoff_location']); ?></td>
            <td><?php echo htmlspecialchars($row['days']); ?></td>
            <td><?php echo htmlspecialchars($row['pickup_time']); ?></td>
            <td><?php echo htmlspecialchars($row['dropoff_time']); ?></td>
            <td><?php echo htmlspecialchars($row['members']); ?></td>
            <td>
                <form class="booking" method="POST" action="approve.php">
                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                    <button type="submit" name="approve_routine">Approve</button>
                    <button type="submit" name="reject_routine">Reject</button>
                </form>

            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <p>No pending routine ride requests.</p>
    <?php endif; ?>

</body>

</html>
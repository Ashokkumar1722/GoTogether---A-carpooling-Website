<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION['email'];

// Fetch booking history for the logged-in user
$sql = "SELECT pickup, dropoff, pickup_date, pickup_time, dropoff_time, required_seats, status 
        FROM bookings 
        WHERE user_email = ?
        ORDER BY pickup_date DESC, pickup_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Booking History</title>
    <link rel="stylesheet" href="assets/booking_history.css">
</head>

<body>
    <h1>My Booking History</h1>

    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Pickup</th>
            <th>Dropoff</th>
            <th>Date</th>
            <th>Pickup Time</th>
            <th>Dropoff Time</th>
            <th>Seats</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['pickup']); ?></td>
            <td><?php echo htmlspecialchars($row['dropoff']); ?></td>
            <td><?php echo htmlspecialchars($row['pickup_date']); ?></td>
            <td><?php echo date('h:i A', strtotime($row['pickup_time'])); ?></td>
            <td><?php echo date('h:i A', strtotime($row['dropoff_time'])); ?></td>
            <td><?php echo htmlspecialchars($row['required_seats']); ?></td>
            <td class="<?php echo strtolower($row['status']); ?>">
                <?php echo ucfirst($row['status']); ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <p>No past bookings found.</p>
    <?php endif; ?>

</body>

</html>
<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

// Fetch top users based on emission savings
$query = "SELECT user_email, 
                 SUM(CO2 + N2O + CO + PM2_5 + VOCs + SO2) AS total_savings 
          FROM emission_savings 
          GROUP BY user_email 
          ORDER BY total_savings DESC 
          LIMIT 10";


$result = $conn->query($query);

// Debugging: Check if query execution failed
if (!$result) {
    die("Query Failed: " . $conn->error);
}

$leaderboard = [];
while ($row = $result->fetch_assoc()) {
    $leaderboard[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Emission Reduction Leaderboard</title>
    <link rel="stylesheet" href="assets/emission_leaderboard.css">
</head>

<body>
    <h1>Go<span> Together</span></h1>
    <h2>Emission Reduction Leaderboard</h2>
    <table>
        <tr>
            <th>Rank</th>
            <th>User</th>
            <th>Total Savings (kg)</th>
        </tr>
        <?php if (!empty($leaderboard)): ?>
        <?php foreach ($leaderboard as $index => $user): ?>
        <tr>
            <td><?php echo $index + 1; ?></td>
            <td><?php echo htmlspecialchars($user['user_email']); ?></td>
            <td><?php echo number_format($user['total_savings'], 2); ?> kg</td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td colspan="3">No emission savings data available.</td>
        </tr>
        <?php endif; ?>
    </table>
</body>

</html>
<?php
session_start();
include 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

if (!isset($_SESSION['email'])) {
    die("Error: User email not found in session. Please log in again.");
}

$user_email = $_SESSION['email']; // Correct session variable

// Fetch total emission savings for the user (only if data exists)
$query = "SELECT SUM(CO2) AS total_co2, SUM(N2O) AS total_n2o, SUM(CO) AS total_co,
                 SUM(PM2_5) AS total_pm25, SUM(VOCs) AS total_vocs, SUM(SO2) AS total_so2
          FROM emission_savings WHERE user_email = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Database Error (Emission Query): " . $conn->error);
}

$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

// Check if user has recorded emission savings
$data_exists = $data && array_sum($data) > 0;

// If data exists, calculate total savings; otherwise, show 0
$total_savings = $data_exists ? array_sum($data) : 0;

// Reward Logic: 100 kg total savings = 1 tree
$trees_awarded = $data_exists ? floor($total_savings / 100) : 0;

// Fetch random eco-tip
$tip_query = "SELECT tip_text FROM eco_tips ORDER BY RAND() LIMIT 1";
$tip_result = $conn->query($tip_query);

if (!$tip_result) {
    die("Database Error (Eco Tips Query): " . $conn->error);
}

$eco_tip = $tip_result->fetch_assoc()['tip_text'] ?? "No tips available.";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Eco Tips & Tree Rewards</title>
    <link rel="stylesheet" href="assets/eco_tips_rewards.css">
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        margin: 0;
        padding: 0;
        text-align: center;
        color: #fff;
    }

    .container {
        max-width: 650px;
        margin: 30px auto;

        padding: 25px;
        border-radius: 20px;
        box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .container:hover {
        transform: scale(1.03);
        box-shadow: 0px 8px 25px rgba(255, 7, 85, 0.89);
    }

    h1 {
        color: #e0f2f1;
        font-size: 36px;
    }

    span {
        text-shadow: 0 4px 4px rgba(225, 5, 75, 0.72);
        color: #ffeb3b;
    }

    h3 {
        color: rgb(230, 0, 134);
        font-weight: 700;
        font-size: 32px;
    }

    h2 {
        color: rgb(0, 152, 229);
    }

    p {
        font-size: 18px;
        color: #e0f2f1;
    }

    .tree-reward {
        font-size: 26px;
        font-weight: bold;
        color: rgb(230, 0, 0);
    }

    .eco-tip {
        font-style: italic;
        color: #ffeb3b;
        margin-top: 10px;
        background: rgba(255, 235, 59, 0.15);
        padding: 12px;
        border-radius: 12px;
    }

    .no-data {
        color: #ff5252;
        font-size: 20px;
        font-weight: bold;
    }

    button {
        background: linear-gradient(135deg, #ff6f61, #ff8a65);
        color: #fff;
        border: none;
        padding: 12px 28px;
        border-radius: 25px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s ease, transform 0.2s ease;
    }

    button:hover {
        background: linear-gradient(135deg, #e53935, #d32f2f);
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(255, 105, 97, 0.8);
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
        width: 12px;
    }

    ::-webkit-scrollbar-thumb {
        background: rgb(33, 35, 34);
        border-radius: 12px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: rgb(48, 52, 50);
    }
    </style>
</head>

<body>
    <h1>Go <span> Together</span></h1>
    <h3>🌿 Eco Tips & Rewards</h3>

    <div class="container">
        <h2>Your Emission Savings</h2>

        <?php if ($data_exists): ?>
        <p><strong>CO2 Saved:</strong> <?php echo number_format($data['total_co2'], 2); ?> g</p>
        <p><strong>N2O Saved:</strong> <?php echo number_format($data['total_n2o'], 2); ?> g</p>
        <p><strong>CO Saved:</strong> <?php echo number_format($data['total_co'], 2); ?> g</p>
        <p><strong>PM2.5 Saved:</strong> <?php echo number_format($data['total_pm25'], 2); ?> g</p>
        <p><strong>VOCs Saved:</strong> <?php echo number_format($data['total_vocs'], 2); ?> g</p>
        <p><strong>SO2 Saved:</strong> <?php echo number_format($data['total_so2'], 2); ?> g</p>

        <h2>🌱 Tree Rewards</h2>
        <p class="tree-reward">You have earned <strong><?php echo $trees_awarded; ?></strong> trees for your savings!
        </p>
        <?php else: ?>
        <p class="no-data">⚠️ No emission savings recorded yet.</p>
        <?php endif; ?>

        <h2>💡 Today's Eco Tip</h2>
        <p class="eco-tip"><?php echo htmlspecialchars($eco_tip); ?></p>
    </div>
</body>

</html>
<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

// Initialize variables
$show_results = false; // Show results only after form submission
$distance = $avg_speed = $actual_time = 0;
$fuel_type = 'petrol';
$idle_hours = $fuel_wasted = $fuel_cost_wasted = 0;
$emissions_released = ["CO2" => 0, "N2O" => 0, "CO" => 0, "PM2_5" => 0, "VOCs" => 0, "SO2" => 0];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $show_results = true; // Enable results after calculation

    // Get User Inputs
    $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : 0;
    $avg_speed = isset($_POST['avg_speed']) ? floatval($_POST['avg_speed']) : 0;
    $actual_time = isset($_POST['actual_time']) ? floatval($_POST['actual_time']) : 0;
    $fuel_type = isset($_POST['fuel_type']) ? $_POST['fuel_type'] : 'petrol';

    // Fetch Expected Time & Fuel Price from `idle_reference`
    $query = "SELECT avg_time_taken, fuel_price FROM idle_reference WHERE fuel_type = ? AND avg_speed = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("si", $fuel_type, $avg_speed);
        $stmt->execute();
        $result = $stmt->get_result();

        $expected_time = 0;
        $fuel_price = 0;

        if ($result->num_rows > 0) {
            $ref = $result->fetch_assoc();
            $expected_time = $ref['avg_time_taken'];
            $fuel_price = $ref['fuel_price'];
        } else {
            echo "<p style='color: red;'>⚠ No expected time found for speed $avg_speed km/h.</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>SQL Error: " . $conn->error . "</p>";
    }


    // Calculate Idle Time, Fuel Wasted & Emissions
    if ($actual_time > $expected_time && $expected_time > 0) {
        $idle_hours = ($actual_time - $expected_time) / 60; 
         // Convert minutes to hours

        // Fuel Consumption Rate (Liters per Hour)
        $fuel_consumption_rate = ($fuel_type === "diesel") ? 1.8 : 2.3;

        // Fuel Wasted & Cost Calculation
        $fuel_wasted = $idle_hours * $fuel_consumption_rate;
        $fuel_cost_wasted = $fuel_wasted * $fuel_price;

       }  
     } // Emission Factors (grams per liter of fuel)
        

// Fetch Random Eco Tip from `idle_tips`
$tip_query = "SELECT tip_text FROM idle_tips ORDER BY RAND() LIMIT 1";
$tip_result = $conn->query($tip_query); // Default tip

if ($tip_result && $tip_result->num_rows > 0) {
    $row = $tip_result->fetch_assoc();
    $idle_tips = $row['tip_text'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Idle Insights</title>
    <link rel="stylesheet" href="assets\idle_time_alert.css">
</head>

<body>

    <h4>⏳ Idle Time Insights</h4>

    <!-- 🚗 Input Form -->
    <form method="POST">
        <label>Distance (km):</label>
        <input type="number" step="0.1" name="distance" required>

        <label>Average Speed (km/h):</label>
        <input type="number" step="0.1" name="avg_speed" required>

        <label>Actual Time Taken (minutes):</label>
        <input type="number" step="1" name="actual_time" required>

        <label>Fuel Type:</label>
        <select name="fuel_type">
            <option value="petrol">Petrol</option>
            <option value="diesel">Diesel</option>
        </select>

        <button type="submit">Calculate</button>
        <?php if ($show_results): ?>
        <h2>Idle Time Summary</h2>
        <p><strong>Idle Hours:</strong> <?php echo number_format($idle_hours, 2); ?> hours</p>
        <p><strong>Fuel Wasted:</strong> <?php echo number_format($fuel_wasted, 2); ?> liters</p>
        <p><strong>Fuel Cost Wasted:</strong> ₹<?php echo number_format($fuel_cost_wasted, 2); ?></p>

        <h2>💡 How It Works</h2>
        <img src="assets\Screenshot_2025-04-05_084634-removebg-preview.png" alt="Idle Formula" width="100%">

        <p>📌 Example Calculation:</p>
        <img src="assets\Screenshot_2025-04-05_091344-removebg-preview.png" alt="Idle Formula" width="60%">




        <h2>🌱 Eco Tip</h2>
        <p>🚗 <?php echo $idle_tips; ?></p>
    </form>


    <?php endif; ?>
</body>

</html>
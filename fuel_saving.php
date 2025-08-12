<?php
session_start();
include 'includes/db.php'; // Ensure database connection is included

// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION['email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $total_passengers = isset($_POST["total_passengers"]) ? max(1, intval($_POST["total_passengers"])) : 1;
    
    // Check if required fields exist
    if (!isset($_POST["mileage"], $_POST["fuel_rate"], $_POST["vehicle_type"], $_POST["fuel_type"])) {
        die("Error: Missing required fields.");
    }

    $mileage = floatval($_POST["mileage"]);
    $fuel_rate = floatval($_POST["fuel_rate"]);
    $vehicle_type = $_POST["vehicle_type"];
    $fuel_type = $_POST["fuel_type"];

    if ($mileage <= 0) {
        die("Error: Mileage must be greater than 0.");
    }

    $total_fuel_wasted = 0;
    $total_cost_wasted = 0;
    $total_fuel_saved = 0;
    $total_cost_saved = 0;

    $passenger_results = [];

    for ($i = 1; $i <= $total_passengers; $i++) {
        if (isset($_POST["distance_$i"]) && is_numeric($_POST["distance_$i"])) {
            $distance = floatval($_POST["distance_$i"]);

            $fuel_wasted = $distance / $mileage;
            $cost_wasted = $fuel_wasted * $fuel_rate;

            $fuel_saved = $fuel_wasted * 0.75;
            $cost_saved = $cost_wasted * 0.75;

            // Store individual results
            $passenger_results[$i] = [
                "distance" => $distance,
                "fuel_wasted" => $fuel_wasted,
                "cost_wasted" => $cost_wasted,
                "fuel_saved" => $fuel_saved,
                "cost_saved" => $cost_saved
            ];

            // Accumulate totals
            $total_fuel_wasted += $fuel_wasted;
            $total_cost_wasted += $cost_wasted;
            $total_fuel_saved += $fuel_saved;
            $total_cost_saved += $cost_saved;

            // Prepare SQL statement and check for errors
            $stmt = $conn->prepare("INSERT INTO fuel_savings 
                (user_email, passenger_number, distance, mileage, fuel_rate, vehicle_type, fuel_type, fuel_wasted, cost_wasted, fuel_saved, cost_saved) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                die("SQL Error: " . $conn->error);
            }

            $stmt->bind_param("siddsssdddd", $user_email, $i, $distance, $mileage, $fuel_rate, $vehicle_type, $fuel_type, $fuel_wasted, $cost_wasted, $fuel_saved, $cost_saved);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel & Cost Saving Calculator</title>
    <link rel="stylesheet" href="assets/fuel_saving.css">
</head>

<body>
    <div class="container">
        <h1>Go<span> Together</span></h1>
        <h2>Fuel & Cost Saving Calculator</h2>

        <form method="post">
            <label>Number of Passengers:</label>
            <input type="number" id="total_passengers" name="total_passengers" required min="1" max="10">

            <div id="passenger_inputs"></div>

            <label>Vehicle Mileage (km/l):</label>
            <input type="number" step="0.1" name="mileage" required>

            <label>Fuel Rate (₹/L):</label>
            <input type="number" step="0.1" name="fuel_rate" required>

            <label>Vehicle Type:</label>
            <select name="vehicle_type" required>
                <option value="car">Car</option>
                <option value="bike">Bike</option>
                <option value="bus">Bus</option>
            </select>

            <label>Fuel Type:</label>
            <select name="fuel_type" required>
                <option value="petrol">Petrol</option>
                <option value="diesel">Diesel</option>
                <option value="cng">CNG</option>
            </select>

            <button type="submit">Calculate Savings</button>
        </form>

        <script>
        document.getElementById('total_passengers').addEventListener('change', function() {
            let container = document.getElementById('passenger_inputs');
            container.innerHTML = '';
            for (let i = 1; i <= this.value; i++) {
                container.innerHTML += `<label>Distance for Passenger ${i} (km):</label>
                                        <input type="number" name="distance_${i}" required step="0.1"><br>`;
            }
        });
        </script>

        <?php if (!empty($passenger_results)): ?>
        <div class="result">
            <h3>🚗 Individual Passenger Savings</h3>
            <table border="1">
                <tr>
                    <th>Passenger</th>
                    <th>Distance (km)</th>
                    <th>Fuel Wasted (L)</th>
                    <th>Cost Wasted (₹)</th>
                    <th>Fuel Saved (L)</th>
                    <th>Cost Saved (₹)</th>
                </tr>
                <?php foreach ($passenger_results as $i => $result): ?>
                <tr>
                    <td>Passenger <?php echo $i; ?></td>
                    <td><?php echo $result["distance"]; ?></td>
                    <td><?php echo $result["fuel_wasted"]; ?></td>
                    <td><?php echo $result["cost_wasted"]; ?></td>
                    <td><?php echo $result["fuel_saved"]; ?></td>
                    <td><?php echo $result["cost_saved"]; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="result">
            <h3>🚗 Without <span style="color: #ff5252;">GoTogether</span></h3>
            <p><strong>Total Fuel Wasted:</strong> <?php echo number_format($total_fuel_wasted, 2); ?> L</p>
            <p><strong>Total Cost Wasted:</strong> ₹<?php echo number_format($total_cost_wasted, 2); ?></p>
        </div>

        <div class="result"
            style="background: #1b5e20; color: #ffffff; padding: 15px; border-radius: 10px; margin-top: 15px;">
            <h3>✅ With <span style="color: #ffeb3b;">GoTogether</span></h3>

            <p><strong>Total Fuel Saved:</strong> <?php echo number_format($total_fuel_saved, 2); ?> L</p>
            <p><strong>Total Cost Saved:</strong> ₹<?php echo number_format($total_cost_saved, 2); ?></p>
        </div>

        <!-- Back Button -->
        <div class="button-container">
            <a href="home.php" class="back-button">⬅️ Back to Home</a>
        </div>

        <?php endif; ?>
    </div>

</body>

</html>
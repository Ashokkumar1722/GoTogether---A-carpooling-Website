<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

$savings = $passenger_savings = [];
$fuel_used = $fuel_cost = $co2_solo = $co2_shared = $co2_saved = $tree_equiv = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $manufacturer = $_POST["manufacturer"] ?? '';
    $model = $_POST["model"] ?? '';
    $year = intval($_POST["year"] ?? 0);
    $mileage = floatval($_POST["mileage"] ?? 0);
    $total_passengers = intval($_POST["total_passengers"] ?? 1);
    $current_year = date("Y");

    $distances = [];
    for ($i = 1; $i <= $total_passengers; $i++) {
        if (!empty($_POST["distance_$i"]) && is_numeric($_POST["distance_$i"])) {
            $distances[$i] = floatval($_POST["distance_$i"]);
        }
    }
    $total_distance = array_sum($distances);

    $query = "SELECT fuel_type FROM car_make WHERE manufacturer = '$manufacturer' AND model = '$model' LIMIT 1";
    $result = mysqli_query($conn, $query);
  $row = mysqli_fetch_assoc($result);
if ($row && !empty($row['fuel_type'])) {
    $fuel_type = strtolower($row['fuel_type']);
} else {
    $fuel_type = 'petrol'; // default fallback
}


    $emission_factors = [
        "petrol" => ["CO2" => 2392, "N2O" => 0.05, "CO" => 2.3, "PM2.5" => 0.02, "VOCs" => 0.3, "SO2" => 0.01, "fuel_price" => 105],
        "diesel" => ["CO2" => 2640, "N2O" => 0.07, "CO" => 1.8, "PM2.5" => 0.04, "VOCs" => 0.2, "SO2" => 0.02, "fuel_price" => 94],
        "cng" => ["CO2" => 1800, "N2O" => 0.03, "CO" => 1.5, "PM2.5" => 0.01, "VOCs" => 0.1, "SO2" => 0.005, "fuel_price" => 76]
    ];

    $car_age_factor = 1 + (($current_year - $year) / 100);
    $factor_set = $emission_factors[$fuel_type];

    if ($mileage > 0 && $total_distance > 0) {
        $fuel_used = $total_distance / $mileage;
        $fuel_cost = $fuel_used * $factor_set['fuel_price'];

        foreach ($factor_set as $gas => $factor) {
            if ($gas == 'fuel_price') continue;
            $savings[$gas] = ($factor * $total_distance) / $mileage * $car_age_factor;
        }

        foreach ($distances as $i => $dist) {
            foreach ($factor_set as $gas => $factor) {
                if ($gas == 'fuel_price') continue;
                $passenger_savings[$i][$gas] = ($factor * $dist) / $mileage * $car_age_factor;
            }
        }

        $co2_solo = ($factor_set['CO2'] * $total_distance) / $mileage * $car_age_factor;
        $co2_shared = $savings['CO2'];
        $co2_saved = $co2_solo - $co2_shared;
        $tree_equiv = $co2_saved / 21000; // 1 tree absorbs ~21kg CO2 per year
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Emission Calculator</title>
    <link rel="stylesheet" href="assets/emssion_calculator.css">
</head>

<body>
    <h1>GO <span>TOGETHER</span></h1>
    <div class="container">
        <h2>Emission Calculator</h2>
        <form method="post">
            <label>Car Manufacturer:</label>
            <select name="manufacturer" id="manufacturer" required>
                <option value="">Select Manufacturer</option>
                <?php
                $result = mysqli_query($conn, "SELECT DISTINCT manufacturer FROM car_make ORDER BY manufacturer");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='{$row['manufacturer']}'>{$row['manufacturer']}</option>";
                }
                ?>
            </select>

            <label>Car Model:</label>
            <select name="model" id="model" required>
                <option value="">Select Model</option>
            </select>
            <label for="fuel_type">Fuel Type:</label>
            <input type="text" id="fuel_type" name="fuel_type"">


            <label>Car Year:</label>
            <select name=" year" id="year" required>
            <option value="">Select Year</option>
            </select>

            <label>Vehicle Mileage (km/l):</label>
            <input type="number" name="mileage" required readonly>


            <label>Number of Passengers:</label>
            <input type="number" name="total_passengers" id="total_passengers" min="1" max="10" required>

            <div id="passenger_inputs"></div>
            <button type="submit">Calculate</button>
        </form>

        <?php if (!empty($savings)): ?>
        <div class="result">
            <h3>Total Trip Emissions</h3>
            <ul>
                <?php foreach ($savings as $gas => $amount): ?>
                <li><strong><?= $gas ?>:</strong>
                    <?= ($amount >= 1000) ? number_format($amount / 1000, 2) . " kg" : number_format($amount, 2) . " g" ?>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="result">
                <h3>Per Passenger Emission</h3>
                <table border="1" width="100%">
                    <tr>
                        <th>Passenger</th>
                        <th>Distance (km)</th>
                        <?php foreach (array_keys($savings) as $gas): ?>
                        <th><?= $gas ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ($passenger_savings as $i => $row): ?>
                    <tr>
                        <td>Passenger <?= $i ?></td>
                        <td><?= $distances[$i] ?></td>
                        <?php foreach ($row as $amount): ?>
                        <td><?= ($amount >= 1000) ? number_format($amount / 1000, 2) . " kg" : number_format($amount, 2) . " g" ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <br><br>
        <br><br>
        <h3>🧮 Emission Calculation Formula</h3>
        <img src="assets\Screenshot_2025-04-04_232904-removebg-preview.png" alt="Emission Calculation Formula"
            width="100%">


        <hr>


        <h3>Fuel Type Examples & Emission Factors</h3>
        <div class="example-section ">
            <!-- Petrol -->


            <div class="example-box">

                <!-- Petrol Example -->
                <h3 style="text-align:center;">🚗 Petrol Vehicle</h3>
                <p><strong>Manufacturer:</strong> Maruti Suzuki<br>
                    <strong>Model:</strong> Swift<br>
                    <strong>Year:</strong> 2018<br>
                    <strong>Mileage:</strong> 20 kmpl<br>
                    <strong>Distance:</strong> 100 km<br>
                    <strong>Car Age Factor:</strong> 1.07
                </p>

                <h4>Emission Factors:</h4>
                <ul>
                    <li><strong>CO₂:</strong> 2392 g/km</li>
                    <li><strong>CO:</strong> 2.3 g/km</li>
                    <li><strong>N₂O:</strong> 0.05 g/km</li>
                    <li><strong>PM2.5:</strong> 0.02 g/km</li>
                    <li><strong>VOCs:</strong> 0.3 g/km</li>
                    <li><strong>SO₂:</strong> 0.01 g/km</li>
                </ul>

                <h4>Results:</h4>
                <ul>
                    <li><strong>CO₂:</strong> (2392 × 100) / 20 × 1.07 = <strong>12.81 kg</strong></li>
                    <li><strong>CO:</strong> (2.3 × 100) / 20 × 1.07 = <strong>12.31 g</strong></li>
                    <li><strong>N₂O:</strong> (0.05 × 100) / 20 × 1.07 = <strong>0.27 g</strong></li>
                    <li><strong>PM2.5:</strong> (0.02 × 100) / 20 × 1.07 = <strong>0.107 g</strong></li>
                    <li><strong>VOCs:</strong> (0.3 × 100) / 20 × 1.07 = <strong>1.605 g</strong></li>
                    <li><strong>SO₂:</strong> (0.01 × 100) / 20 × 1.07 = <strong>0.053 g</strong></li>
                </ul>
            </div>

            <!-- Diesel Example -->
            <div class="example-box">
                <h3 style="text-align:center;">🚙 Diesel Vehicle</h3>
                <p><strong>Manufacturer:</strong> Ford<br>
                    <strong>Model:</strong> EcoSport<br>
                    <strong>Year:</strong> 2018<br>
                    <strong>Mileage:</strong> 20 kmpl<br>
                    <strong>Distance:</strong> 100 km<br>
                    <strong>Car Age Factor:</strong> 1.07
                </p>

                <h4>Emission Factors:</h4>
                <ul>
                    <li><strong>CO₂:</strong> 2640 g/km</li>
                    <li><strong>CO:</strong> 1.8 g/km</li>
                    <li><strong>N₂O:</strong> 0.07 g/km</li>
                    <li><strong>PM2.5:</strong> 0.04 g/km</li>
                    <li><strong>VOCs:</strong> 0.2 g/km</li>
                    <li><strong>SO₂:</strong> 0.02 g/km</li>
                </ul>

                <h4>Results:</h4>
                <ul>
                    <li><strong>CO₂:</strong> (2640 × 100) / 20 × 1.07 = <strong>14.13 kg</strong></li>
                    <li><strong>CO:</strong> (1.8 × 100) / 20 × 1.07 = <strong>9.63 g</strong></li>
                    <li><strong>N₂O:</strong> (0.07 × 100) / 20 × 1.07 = <strong>0.374 g</strong></li>
                    <li><strong>PM2.5:</strong> (0.04 × 100) / 20 × 1.07 = <strong>0.214 g</strong></li>
                    <li><strong>VOCs:</strong> (0.2 × 100) / 20 × 1.07 = <strong>1.07 g</strong></li>
                    <li><strong>SO₂:</strong> (0.02 × 100) / 20 × 1.07 = <strong>0.107 g</strong></li>
                </ul>
            </div>

            <!-- CNG Example -->
            <div class="example-box">
                <h3 style="text-align:center;">🚐 CNG Vehicle</h3>
                <p><strong>Manufacturer:</strong> Honda<br>
                    <strong>Model:</strong> Amaze<br>
                    <strong>Year:</strong> 2018<br>
                    <strong>Mileage:</strong> 20 kmpl<br>
                    <strong>Distance:</strong> 100 km<br>
                    <strong>Car Age Factor:</strong> 1.07
                </p>

                <h4>Emission Factors:</h4>
                <ul>
                    <li><strong>CO₂:</strong> 1800 g/km</li>
                    <li><strong>CO:</strong> 1.5 g/km</li>
                    <li><strong>N₂O:</strong> 0.03 g/km</li>
                    <li><strong>PM2.5:</strong> 0.01 g/km</li>
                    <li><strong>VOCs:</strong> 0.1 g/km</li>
                    <li><strong>SO₂:</strong> 0.005 g/km</li>
                </ul>

                <h4>Results:</h4>
                <ul>
                    <li><strong>CO₂:</strong> (1800 × 100) / 20 × 1.07 = <strong>9.63 kg</strong></li>
                    <li><strong>CO:</strong> (1.5 × 100) / 20 × 1.07 = <strong>8.03 g</strong></li>
                    <li><strong>N₂O:</strong> (0.03 × 100) / 20 × 1.07 = <strong>0.16 g</strong></li>
                    <li><strong>PM2.5:</strong> (0.01 × 100) / 20 × 1.07 = <strong>0.053 g</strong></li>
                    <li><strong>VOCs:</strong> (0.1 × 100) / 20 × 1.07 = <strong>0.535 g</strong></li>
                    <li><strong>SO₂:</strong> (0.005 × 100) / 20 × 1.07 = <strong>0.267 g</strong></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>


    </div>
    </div>




    <script>
    // Fetch Models based on Manufacturer
    document.getElementById("manufacturer").addEventListener("change", function() {
        fetch("fetch_models.php?manufacturer=" + this.value)
            .then(res => res.json())
            .then(data => {
                let modelSelect = document.getElementById("model");
                modelSelect.innerHTML = "<option value=''>Select Model</option>";
                data.forEach(model => {
                    let opt = document.createElement("option");
                    opt.value = model;
                    opt.textContent = model;
                    modelSelect.appendChild(opt);
                });

                // Clear year, mileage, fuel when manufacturer changes
                document.getElementById("year").innerHTML = "<option value=''>Select Year</option>";
                document.getElementById("fuel_type").value = '';
                document.querySelector("input[name='mileage']").value = '';
            });
    });

    // Fetch Years, Fuel Type & Mileage when Model is selected
    document.getElementById("model").addEventListener("change", function() {
        let manu = document.getElementById("manufacturer").value;
        let model = this.value;

        // Fetch years & mileage together
        fetch(`fetch_years.php?manufacturer=${manu}&model=${model}`)
            .then(res => res.json())
            .then(data => {
                // Populate year dropdown
                let yearSelect = document.getElementById("year");
                yearSelect.innerHTML = "<option value=''>Select Year</option>";
                data.years.forEach(year => {
                    let opt = document.createElement("option");
                    opt.value = year;
                    opt.textContent = year;
                    yearSelect.appendChild(opt);
                });

                // Auto-fill mileage
                if (data.mileage) {
                    document.querySelector("input[name='mileage']").value = data.mileage;
                }
            });

        // Fetch fuel type separately
        fetch(`fetch_fuel.php?manufacturer=${manu}&model=${model}`)
            .then(res => res.text())
            .then(fuel => {
                document.getElementById("fuel_type").value = fuel;
            });
    });

    // Handle passenger count dynamically
    document.getElementById("total_passengers").addEventListener("change", function() {
        let container = document.getElementById("passenger_inputs");
        container.innerHTML = "";
        for (let i = 1; i <= this.value; i++) {
            container.innerHTML += `<label>Distance for Passenger ${i} (km):</label>
                                <input type="number" name="distance_${i}" required><br>`;
        }
    });
    </script>
</body>

</html>
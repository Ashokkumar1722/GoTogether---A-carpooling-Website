<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION['email']; // Get logged-in user's email

// Function to safely prepare and execute a query
function fetch_value($conn, $query, $email) {
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    } else {
        die("Error in SQL Query: " . $conn->error);
    }
}

// Fetch total CO₂ savings
$query = "SELECT SUM(CO2) AS total_co2 FROM emission_savings WHERE user_email = ?";
$data = fetch_value($conn, $query, $user_email);
$total_co2 = $data['total_co2'] ?? 0;

// Fetch total fuel savings
$query = "SELECT SUM(fuel_saved) AS total_fuel, AVG(fuel_rate) AS avg_fuel_rate FROM fuel_savings WHERE user_email = ?";
$data = fetch_value($conn, $query, $user_email);
$total_fuel = $data['total_fuel'] ?? 0;
$avg_fuel_price = $data['avg_fuel_price'] ?? 100; // Default ₹100 if no data

// Calculate cost savings
$cost_savings = $total_fuel * $avg_fuel_price;

// Motivational message
$share_text = urlencode(" I’m making a difference with GoTogether! 

I’ve saved $total_co2 kg of CO₂ emissions and $total_fuel liters of fuel, reducing my carbon footprint while saving ₹$cost_savings!

Join me in making a positive impact! Start carpooling with GoTogether today! 

#GoTogether #CarpoolForChange #EcoFriendlyTravel");

// Share URLs
$whatsapp_url = "https://api.whatsapp.com/send?text=$share_text";
$twitter_url = "https://twitter.com/intent/tweet?text=$share_text";
$facebook_url = "https://www.facebook.com/sharer/sharer.php?u=https://gotogether.com";
$linkedin_url = "https://www.linkedin.com/sharing/share-offsite/?url=https://gotogether.com";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Your Impact</title>
    <link rel="stylesheet" href="assets/social_share.css">
</head>

<body>
    <div class="container">
        <h1>🚀 Share Your Impact with GoTogether! 🌍</h1>
        <p>You’ve saved <strong><?php echo number_format($total_co2, 2); ?> kg</strong> of CO₂ and
            <strong><?php echo number_format($total_fuel, 2); ?> L</strong> of fuel.
        </p>
        <p>That’s a cost saving of <strong>₹<?php echo number_format($cost_savings, 2); ?></strong>!</p>

        <h2>📢 Spread the Word!</h2>
        <p>Let your friends know how you’re helping the planet. Share your impact on social media!</p>

        <div class="social-buttons">
            <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="whatsapp">Share on WhatsApp</a>
            <a href="<?php echo $twitter_url; ?>" target="_blank" class="twitter">Tweet on Twitter</a>
            <a href="<?php echo $facebook_url; ?>" target="_blank" class="facebook">Share on Facebook</a>
            <a href="<?php echo $linkedin_url; ?>" target="_blank" class="linkedin">Post on LinkedIn</a>
        </div>
    </div>
</body>

</html>
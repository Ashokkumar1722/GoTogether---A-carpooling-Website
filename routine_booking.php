<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle routine booking submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_routine_ride'])) {
    $pickup_location = $_POST['pickup_location'];
    $dropoff_location = $_POST['dropoff_location'];
    $days = implode(",", $_POST['days']); // Convert array to comma-separated string
    $pickup_time = $_POST['pickup_time'];
    $dropoff_time = $_POST['dropoff_time'];
    $members = $_POST['members'];

    $stmt = $conn->prepare("INSERT INTO routine_bookings (user_id, pickup_location, dropoff_location, days, pickup_time, dropoff_time, members) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $user_id, $pickup_location, $dropoff_location, $days, $pickup_time, $dropoff_time, $members);

    if ($stmt->execute()) {
        echo "<script>alert('Routine Booking Successful! Waiting for approval.'); window.location.href='routine_booking.php';</script>";
    } else {
        echo "<script>alert('Error booking routine ride!');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Routine Ride Booking</title>
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        margin: 0;
        padding: 0;
        text-align: center;
        color: #fff;
    }

    .container,
    form {
        max-width: 650px;
        margin: 50px auto;
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .container:hover,
    form:hover {
        transform: scale(1.03);
        box-shadow: 0px 8px 25px rgba(255, 11, 93, 0.65);
    }

    h1 {
        color: aliceblue;
        font-size: 3rem;
        font-style: oblique;
    }

    span {
        color: aqua;
        text-shadow: 2px 3px red;
    }

    h2 {
        color: rgb(230, 0, 138);
        font-weight: 700;
        font-size: 2rem;
        text-shadow: 2px 0px greenyellow;
        ;
    }

    label {
        color: rgb(57, 176, 178);
        font-weight: bold;
        margin-top: 10px;
        display: inline-block;
    }

    input[type="text"],
    input[type="time"],
    input[type="number"] {
        width: 90%;
        padding: 10px;
        margin: 10px 0;
        border: 2px solid rgb(9, 204, 238);
        border-radius: 8px;
        background-color: rgb(4, 65, 100);
        color: #fff;
    }

    input[type="checkbox"] {
        accent-color: rgb(230, 0, 150);
        margin: 5px 5px 10px 0;
    }

    button {
        background: linear-gradient(135deg, rgb(73, 184, 212), rgb(175, 10, 78));
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
        background: rgb(61, 68, 65);
        border-radius: 12px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: rgb(43, 46, 44);
    }
    </style>
</head>

<body>
    <h1>Go<span> Together</span></h1>
    <h2>Routine Ride Booking</h2>
    <form method="POST">
        <input type="text" name="pickup_location" placeholder="Pickup Location" required>
        <input type="text" name="dropoff_location" placeholder="Dropoff Location" required>

        <label>Select Days:</label><br>
        <input type="checkbox" name="days[]" value="Monday"> Monday
        <input type="checkbox" name="days[]" value="Tuesday"> Tuesday
        <input type="checkbox" name="days[]" value="Wednesday"> Wednesday
        <input type="checkbox" name="days[]" value="Thursday"> Thursday
        <input type="checkbox" name="days[]" value="Friday"> Friday
        <input type="checkbox" name="days[]" value="Saturday"> Saturday
        <input type="checkbox" name="days[]" value="Sunday"> Sunday

        <input type="time" name="pickup_time" required>
        <input type="time" name="dropoff_time" required>
        <input type="number" name="members" placeholder="Number of Members" required>
        <button type="submit" name="book_routine_ride">Submit Request</button>
    </form>
</body>

</html>
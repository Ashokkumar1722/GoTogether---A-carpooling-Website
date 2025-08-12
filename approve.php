<?php
session_start();
include 'includes/db.php';
require 'includes/mailer.php'; // Include mailer function

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve'])) {
        $booking_id = $_POST['booking_id'];
        $user_email = $_POST['user_email'];

        // Approve normal ride request
        $update_sql = "UPDATE bookings SET status = 'approved' WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            // Send email notification
            $subject = "Ride Request Approved - GoTogether";
            $message = "<h3>Your ride request has been approved!</h3>
                        <p>Check your dashboard for details.</p>";
            sendEmail($user_email, $subject, $message);
            echo "<script>alert('Booking approved!'); window.location.href='offer_ride.php';</script>";
        }
        $stmt->close();
    }

    if (isset($_POST['reject'])) {
        $booking_id = $_POST['booking_id'];
        $user_email = $_POST['user_email'];

        // Reject normal ride request
        $update_sql = "UPDATE bookings SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            // Send email notification
            $subject = "Ride Request Rejected - GoTogether";
            $message = "<h3>Your ride request has been rejected.</h3>
                        <p>You can try booking another ride.</p>";
            sendEmail($user_email, $subject, $message);
            echo "<script>alert('Booking rejected!'); window.location.href='offer_ride.php';</script>";
        }
        $stmt->close();
    }

    if (isset($_POST['approve_routine'])) {
        $routine_id = $_POST['booking_id'];

        // Approve routine ride request
        $update_sql = "UPDATE routine_bookings SET status = 'approved' WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $routine_id);
        if ($stmt->execute()) {
            // Fetch user email from routine_bookings
            $query = "SELECT user_id FROM routine_bookings WHERE id = ?";
            $stmt2 = $conn->prepare($query);
            $stmt2->bind_param("i", $routine_id);
            $stmt2->execute();
            $stmt2->bind_result($user_id);
            $stmt2->fetch();
            $stmt2->close();

            // Fetch user email from users table
            $query2 = "SELECT email FROM users WHERE id = ?";
            $stmt3 = $conn->prepare($query2);
            $stmt3->bind_param("i", $user_id);
            $stmt3->execute();
            $stmt3->bind_result($user_email);
            $stmt3->fetch();
            $stmt3->close();

            // Send email notification
            $subject = "Routine Ride Request Approved - GoTogether";
            $message = "<h3>Your routine ride request has been approved!</h3>
                        <p>Check your scheduled rides.</p>";
            sendEmail($user_email, $subject, $message);
            echo "<script>alert('Routine booking approved!'); window.location.href='offer_ride.php';</script>";
        }
        $stmt->close();
    }

    if (isset($_POST['reject_routine'])) {
        $routine_id = $_POST['booking_id'];

        // Reject routine ride request
        $update_sql = "UPDATE routine_bookings SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $routine_id);
        if ($stmt->execute()) {
            // Fetch user email from routine_bookings
            $query = "SELECT user_id FROM routine_bookings WHERE id = ?";
            $stmt2 = $conn->prepare($query);
            $stmt2->bind_param("i", $routine_id);
            $stmt2->execute();
            $stmt2->bind_result($user_id);
            $stmt2->fetch();
            $stmt2->close();

            // Fetch user email from users table
            $query2 = "SELECT email FROM users WHERE id = ?";
            $stmt3 = $conn->prepare($query2);
            $stmt3->bind_param("i", $user_id);
            $stmt3->execute();
            $stmt3->bind_result($user_email);
            $stmt3->fetch();
            $stmt3->close();

            // Send email notification
            $subject = "Routine Ride Request Rejected - GoTogether";
            $message = "<h3>Your routine ride request has been rejected.</h3>
                        <p>You can try booking another routine ride.</p>";
            sendEmail($user_email, $subject, $message);
            echo "<script>alert('Routine booking rejected!'); window.location.href='offer_ride.php';</script>";
        }
        $stmt->close();
    }
}
?>
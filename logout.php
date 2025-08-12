<?php
session_start();
session_destroy(); // Destroy all sessions
header("Location: login.html"); // Redirect to login page
exit();
?>
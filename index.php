<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoTogether - Home</title>
    <link rel="stylesheet" href="assets/home.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const loader = document.getElementById("loader");
        const content = document.getElementById("content");

        setTimeout(function() {
            if (loader) loader.style.display = "none";
            if (content) content.style.display = "block";
        }, 2000); // Loader delay - adjust as needed
    });
    </script>
</head>

<body>
    <!-- SVG Loader -->
    <div class="bike-loader" id="loader">
        <svg class="bike" viewBox="0 0 48 30" width="48px" height="30px">
            <!-- SVG Content -->
            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1">
                <g transform="translate(9.5,19)">
                    <circle class="bike__tire" r="9" stroke-dasharray="56.549 56.549" />
                    <g class="bike__spokes-spin" stroke-dasharray="31.416 31.416" stroke-dashoffset="-23.562">
                        <circle class="bike__spokes" r="5" />
                        <circle class="bike__spokes" r="5" transform="rotate(180,0,0)" />
                    </g>
                </g>
                <g transform="translate(24,19)">
                    <g class="bike__pedals-spin" stroke-dasharray="25.133 25.133" stroke-dashoffset="-21.991"
                        transform="rotate(67.5,0,0)">
                        <circle class="bike__pedals" r="4" />
                        <circle class="bike__pedals" r="4" transform="rotate(180,0,0)" />
                    </g>
                </g>
                <g transform="translate(38.5,19)">
                    <circle class="bike__tire" r="9" stroke-dasharray="56.549 56.549" />
                    <g class="bike__spokes-spin" stroke-dasharray="31.416 31.416" stroke-dashoffset="-23.562">
                        <circle class="bike__spokes" r="5" />
                        <circle class="bike__spokes" r="5" transform="rotate(180,0,0)" />
                    </g>
                </g>
                <polyline class="bike__seat" points="14 3,18 3" stroke-dasharray="5 5" />
                <polyline class="bike__body" points="16 3,24 19,9.5 19,18 8,34 7,24 19" stroke-dasharray="79 79" />
                <path class="bike__handlebars" d="m30,2h6s1,0,1,1-1,1" stroke-dasharray="10 10" />
                <polyline class="bike__front" points="32.5 2,38.5 19" stroke-dasharray="19 19" />
            </g>
        </svg>
    </div>

    <!-- Main Content (Initially Hidden) -->
    <div id="content" style="display: none;">
        <header>
            <h1>JOIN THE JOURNEY WITH GO<span> TOGETHER</span></h1>
            <p>Carpooling isn't just about saving money — it's about saving the planet.</p>
        </header>

        <nav>
            <ul>
                <li><a href="offer_ride.php">Offer Ride</a></li>
                <li><a href="book_ride.php">Book Ride</a></li>
                <li><a href="booking_history.php">Booking History</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <div class="container">
            <section class="features">
                <div class="feature">
                    <i class="fas fa-trophy"></i>
                    <h2>Emission Leaderboard</h2>
                    <p>Track your CO₂ savings and see how you rank among eco-conscious riders.</p>
                    <button onclick="location.href='emission_leaderboard.php'">View</button>
                </div>

                <div class="feature">
                    <i class="fas fa-calculator"></i>
                    <h2>Emission Reduction Calculator</h2>
                    <p>Estimate your environmental impact by calculating CO₂ and other emissions.</p>
                    <button onclick="location.href='emission_calculator.php'">Calculate</button>
                </div>

                <div class="feature">
                    <i class="fas fa-share-alt"></i>
                    <h2>Social Media Sharing</h2>
                    <p>Showcase your eco-friendly efforts by sharing your impact online.</p>
                    <button onclick="location.href='social_sharing.php'">Share</button>
                </div>

                <div class="feature">
                    <i class="fas fa-bus-alt"></i>
                    <h2>Alternative Transport Suggestions</h2>
                    <p>Find eco-friendly travel alternatives when carpooling isn’t available.</p>
                    <button onclick="location.href='alt_transport.php'">Suggest</button>
                </div>

                <div class="feature">
                    <i class="fas fa-gas-pump"></i>
                    <h2>Fuel & Cost Saving</h2>
                    <p>Calculate your fuel savings and reduce travel expenses effectively.</p>
                    <button onclick="location.href='fuel_saving.php'">Calculate</button>
                </div>

                <div class="feature">
                    <i class="fas fa-school"></i>
                    <h2>College/School Routine Booking</h2>
                    <p>Book routine trips easily for students and staff members.</p>
                    <button onclick="location.href='routine_booking.php'">Book</button>
                </div>

                <div class="feature">
                    <i class="fas fa-leaf"></i>
                    <h2>Eco Tips & Tree Rewards</h2>
                    <p>Get daily tips to reduce emissions and earn virtual trees as rewards.</p>
                    <button onclick="location.href='eco_tips_rewards.php'">View Tips</button>
                </div>

                <div class="feature">
                    <i class="fas fa-tree"></i>
                    <h2>Idle Insights</h2>
                    <p>Monitor idle time, reduce fuel waste, and earn virtual trees for smarter driving habits</p>
                    <button onclick="location.href='idle_insights.php'">Check Rewards</button>
                </div>
            </section>

            </section>
        </div>
    </div>
</body>

</html>
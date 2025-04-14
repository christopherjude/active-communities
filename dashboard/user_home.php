<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registered') {
    header("Location: ../index.php");
    exit();
}
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Stats
$bookings = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE user_id = $user_id")->fetch_assoc()['total'];
$reviews = $conn->query("SELECT COUNT(*) AS total FROM reviews WHERE reviewer_id = $user_id")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - Active Communities</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('../includes/header.php'); ?>

        <div class="container">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
            <p>Explore and manage your activity:</p>

            <div class="dashboard-cards">
                <div class="card">
                    <h3>Book Sessions</h3>
                    <p>Explore and book cycling training sessions.</p>
                    <a href="../sessions/list.php">Browse Sessions →</a>
                </div>

                <div class="card">
                    <h3>Leave or View Reviews</h3>
                    <p>You’ve written <?php echo $reviews; ?> reviews.</p>
                    <a href="../reviews/my_reviews.php">Manage Reviews →</a>
                </div>

                <div class="card">
                    <h3>Your Bookings</h3>
                    <p>You’ve booked <?php echo $bookings; ?> session<?php echo $bookings != 1 ? 's' : ''; ?>.</p>
                    <a href="../sessions/my_bookings.php">View Bookings →</a>
                </div>

                <div class="card">
                    <h3>Instructor Profiles</h3>
                    <p>Browse and learn about our instructors.</p>
                    <a href="../instructors/list.php">View Instructors →</a>
                </div>

                <div class="card">
                    <h3>Contact the Leader</h3>
                    <p>Need help? Message the leader directly.</p>
                    <a href="../contact/form.php">Send Message →</a>
                </div>

                <div class="card">
                    <h3>Apply to Be an Instructor</h3>
                    <p>Ready to help others? Submit your application to become a cycling instructor.</p>
                    <a href="../instructors/apply.php">Apply Now →</a>
                </div>
            </div>
        </div>

        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>

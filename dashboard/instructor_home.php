<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
$user_id = $_SESSION['user_id'];

// Get number of assigned sessions (bookings where this instructor is assigned)
$assigned_sessions = $conn->query("
    SELECT COUNT(*) AS total 
    FROM training_sessions 
    WHERE instructor_id = $user_id
")->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Instructor Dashboard - Active Communities</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('../includes/header.php'); ?>

        <div class="container">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (Instructor)</h2>
            <p>Manage your activity and share your cycling knowledge:</p>

            <div class="dashboard-cards">
                <div class="card">
                    <h3>Assigned Sessions</h3>
                    <p>You’ve been assigned to <?php echo $assigned_sessions; ?> session<?php echo $assigned_sessions != 1 ? 's' : ''; ?>.</p>
                    <a href="../sessions/instructor_sessions.php">View Sessions →</a>
                </div>

                <div class="card">
                    <h3>Your Profile</h3>
                    <p>Create or update your bio and experience.</p>
                    <a href="../instructors/profile.php">Edit Profile →</a>
                </div>

                <div class="card">
                    <h3>Post Helpful Info</h3>
                    <p>Share cycling tips or session insights.</p>
                    <a href="../instructors/tips.php">Post Info →</a>
                </div>
            </div>
        </div>

        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header("Location: ../index.php");
    exit();
}
require_once '../includes/db.php';

// Fetch quick stats
$leader_id = $_SESSION['user_id'];

// Count instructor applications
$pending_apps = $conn->query("SELECT COUNT(*) AS total FROM instructor_applications WHERE status = 'pending'")->fetch_assoc()['total'];

// Count contact messages
$unread_msgs = $conn->query("SELECT COUNT(*) AS total FROM contact_forms WHERE status = 'unread'")->fetch_assoc()['total'];

// Count upcoming sessions
$upcoming_sessions = $conn->query("SELECT COUNT(*) AS total FROM training_sessions WHERE created_by = $leader_id AND date >= CURDATE()")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leader Dashboard - Active Communities</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('../includes/header.php'); ?>

        <div class="container">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (Leader)</h2>
            <p>Here’s an overview of what needs your attention:</p>

            <div class="dashboard-cards">
                <div class="card">
                    <h3><?php echo $pending_apps; ?> Instructor Applications</h3>
                    <p>Pending approval from applicants.</p>
                    <a href="instructor_approvals.php">Review Applications →</a>
                </div>

                <div class="card">
                    <h3><?php echo $upcoming_sessions; ?> Upcoming Sessions</h3>
                    <p>You are hosting these sessions.</p>
                    <a href="leader_sessions.php">Manage Sessions →</a>
                </div>

                <div class="card">
                    <h3><?php echo $unread_msgs; ?> Unread Messages</h3>
                    <p>From contact forms submitted by users.</p>
                    <a href="contact_inbox.php">Check Inbox →</a>
                </div>
            </div>
        </div>

        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>

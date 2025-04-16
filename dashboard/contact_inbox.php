<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header("Location: ../index.php");
    exit();
}
require_once '../includes/db.php';

// ✅ Mark all unread messages as read
$conn->query("UPDATE contact_forms SET status = 'read' WHERE status = 'unread'");

// Fetch all messages
$messages = $conn->query("SELECT f.*, u.name, u.email 
                          FROM contact_forms f 
                          JOIN users u ON f.user_id = u.id 
                          ORDER BY f.id DESC");
?>


<!DOCTYPE html>
<html>
<head>
    <title>Contact Messages</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('../includes/header.php'); ?>
        <div class="container">
            <h2>Inbox</h2>
            <?php while($row = $messages->fetch_assoc()): ?>
                <div class="card">
                    <h3>From: <?php echo htmlspecialchars($row['name']); ?> (<?php echo $row['email']; ?>)</h3>
                    <p><strong>Status:</strong> <?php echo ucfirst($row['status']); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>

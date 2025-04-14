<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle tip submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tip = trim($_POST['tip']);
    if (!empty($tip)) {
        $stmt = $conn->prepare("INSERT INTO instructor_tips (user_id, tip) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $tip);
        if ($stmt->execute()) {
            $success = "Your tip has been posted!";
        } else {
            $error = "Failed to post tip. Please try again.";
        }
    } else {
        $error = "Tip cannot be empty.";
    }
}

// Fetch tips
$tips = $conn->prepare("SELECT tip, created_at FROM instructor_tips WHERE user_id = ? ORDER BY created_at DESC");
$tips->bind_param("i", $user_id);
$tips->execute();
$results = $tips->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post Helpful Tips</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .tips-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .tip-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
        }

        .tip-card small {
            color: #666;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include('../includes/header.php'); ?>

    <div class="container">
        <h2>Share Helpful Cycling Info</h2>
        <p>Post safety tips, riding strategies, or confidence-building advice for beginners.</p>

        <?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($success): ?><p style="color:green;"><?php echo $success; ?></p><?php endif; ?>

        <form method="post" class="tips-form">
            <textarea name="tip" rows="4" placeholder="Write your tip here..." required></textarea>
            <button type="submit">Post Tip</button>
        </form>

        <hr style="margin: 2rem 0;">

        <h3>Your Posted Tips</h3>

        <?php if ($results->num_rows > 0): ?>
            <?php while ($row = $results->fetch_assoc()): ?>
                <div class="tip-card">
                    <p><?php echo nl2br(htmlspecialchars($row['tip'])); ?></p>
                    <small>Posted on: <?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You haven't posted any tips yet.</p>
        <?php endif; ?>
    </div>

    <?php include('../includes/footer.php'); ?>
</div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registered') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$check = $conn->prepare("SELECT status FROM instructor_applications WHERE user_id = ?");
$check->bind_param("i", $user_id);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    $row = $check_result->fetch_assoc();
    $status = $row['status'];
    $error = "You have already applied. Current status: <strong>$status</strong>";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notes = trim($_POST['notes']);

    $stmt = $conn->prepare("INSERT INTO instructor_applications (user_id, status, notes) VALUES (?, 'pending', ?)");
    $stmt->bind_param("is", $user_id, $notes);
    
    if ($stmt->execute()) {
        $success = "Your application has been submitted successfully!";
    } else {
        $error = "An error occurred. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Apply to be an Instructor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('../includes/header.php'); ?>
        <div class="container">
            <h2>Instructor Application</h2>
            <p>Tell us why you’d make a great cycling instructor. Your application will be reviewed by the Cycling Leader.</p>

            <?php if ($error): ?>
                <div style="color: red; margin-bottom: 1rem;"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div style="color: green; margin-bottom: 1rem;"><?php echo $success; ?></div>
                <p><a href="../dashboard/user_home.php">← Back to Dashboard</a></p>
            <?php elseif (!$error): ?>
                <form method="post">
                    <label for="notes">Why do you want to become an instructor?</label>
                    <textarea name="notes" id="notes" rows="5" required></textarea>
                    <button type="submit">Submit Application</button>
                </form>
            <?php endif; ?>
        </div>
        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>

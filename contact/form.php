<?php
session_start();
require_once '../includes/db.php';

$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

    $stmt = $conn->prepare("INSERT INTO contact_forms (user_id, message) VALUES (?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("is", $user_id, $message);
    if ($stmt->execute()) {
        $success = "Thank you for your message! We'll get back to you soon.";
    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Us</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('../includes/header.php'); ?>

        <div class="container">
            <h2>Contact the Cycling Leader</h2>
            <p>If you have questions, feedback, or would like to report something, send us a message below:</p>

            <?php if ($success): ?>
                <p style="color: green;"><?php echo $success; ?></p>
            <?php endif; ?>

            <form method="post">
                <textarea name="message" rows="6" placeholder="Your message here..." required></textarea>
                <button type="submit">Send Message</button>
            </form>
        </div>

        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';

// Handle approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_user_id'])) {
    $user_id = intval($_POST['approve_user_id']);

    $stmt = $conn->prepare("INSERT INTO instructors (user_id) VALUES (?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $delete = $conn->prepare("DELETE FROM instructor_applications WHERE user_id = ?");
    $delete->bind_param("i", $user_id);
    $delete->execute();

    header("Location: instructor_approvals.php");
    exit();
}

// Fetch pending applications
$applications = $conn->query("SELECT u.id, u.name, u.email, a.notes 
    FROM instructor_applications a 
    JOIN users u ON u.id = a.user_id 
    WHERE a.status = 'pending'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Instructor Applications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('../includes/header.php'); ?>
        <div class="container">
            <h2>Pending Instructor Applications</h2>
            <?php while($row = $applications->fetch_assoc()): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p>Email: <?php echo $row['email']; ?></p>
                    <p>Notes: <?php echo nl2br($row['notes']); ?></p>
                    <form method="post">
                        <input type="hidden" name="approve_user_id" value="<?php echo $row['id']; ?>">
                        <button type="submit">Approve</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>

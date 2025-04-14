<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
$instructor_id = $_SESSION['user_id'];

// Get sessions where this instructor has bookings
$sql = "
    SELECT ts.id, ts.title, ts.date, ts.time, ts.route, ts.grade,
           (SELECT COUNT(*) FROM bookings WHERE session_id = ts.id) AS booking_count
    FROM training_sessions ts
    WHERE ts.instructor_id = ?
    ORDER BY ts.date ASC, ts.time ASC
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Assigned Sessions</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('../includes/header.php'); ?>

        <div class="container">
            <h2>Your Assigned Training Sessions</h2>

            <?php if ($result->num_rows > 0): ?>
                <ul class="session-list">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li style="margin-bottom: 1.5rem;">
                            <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                            <small><?php echo $row['date'] . ' at ' . substr($row['time'], 0, 5); ?></small><br>
                            <small>Route: <?php echo htmlspecialchars($row['route']); ?> | Grade: <?php echo htmlspecialchars($row['grade']); ?></small><br>
                            <small>Bookings: <?php echo $row['booking_count']; ?></small>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You have not been assigned to any sessions yet.</p>
            <?php endif; ?>
        </div>

        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>

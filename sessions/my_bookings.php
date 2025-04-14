<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registered') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
$user_id = $_SESSION['user_id'];

$sql = "
    SELECT ts.title, ts.date, ts.time, ts.route, ts.grade, u.name AS instructor_name, b.status
    FROM bookings b
    JOIN training_sessions ts ON b.session_id = ts.id
    JOIN users u ON ts.instructor_id = u.id
    WHERE b.user_id = ?
    ORDER BY ts.date ASC, ts.time ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings - Active Communities</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <?php include('../includes/header.php'); ?>
    <div class="container">
        <h2>My Booked Sessions</h2>

        <?php if ($result->num_rows > 0): ?>
            <ul class="session-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="session-card">
                        <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                        <span><?php echo $row['date'] . ' at ' . substr($row['time'], 0, 5); ?></span><br>
                        <span>Instructor: <?php echo htmlspecialchars($row['instructor_name']); ?></span><br>
                        <span>Route: <?php echo htmlspecialchars($row['route']); ?> | Grade: <?php echo htmlspecialchars($row['grade']); ?></span><br>
                        <?php
                            $datetime = strtotime($row['date'] . ' ' . $row['time']);
                            $is_completed = $datetime < time();
                        ?>
                        <small>Status: <?php echo $is_completed ? 'Completed' : 'Upcoming'; ?></small>

                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>You haven’t booked any sessions yet.</p>
        <?php endif; ?>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>
</body>
</html>

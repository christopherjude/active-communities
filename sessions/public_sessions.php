<?php
session_start();
require_once '../includes/db.php';

// Fetch upcoming sessions
$upcoming_sql = "SELECT ts.*, u.name AS instructor_name
                 FROM training_sessions ts
                 JOIN users u ON ts.instructor_id = u.id
                 WHERE CONCAT(ts.date, ' ', ts.time) >= NOW()
                 ORDER BY ts.date ASC, ts.time ASC";
$upcoming_result = $conn->query($upcoming_sql);

// Fetch completed sessions and their reviews
$completed_sql = "SELECT 
                    ts.id AS session_id,
                    ts.title,
                    ts.date,
                    ts.time,
                    ts.route,
                    ts.grade,
                    u.name AS instructor_name,
                    r.rating,
                    r.comment,
                    r.created_at,
                    ru.name AS reviewer_name
                FROM training_sessions ts
                JOIN users u ON ts.instructor_id = u.id
                LEFT JOIN reviews r ON r.session_id = ts.id
                LEFT JOIN users ru ON r.reviewer_id = ru.id
                WHERE CONCAT(ts.date, ' ', ts.time) < NOW()
                ORDER BY ts.date DESC, ts.time DESC, r.created_at DESC";
$completed_result = $conn->query($completed_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Public Training Sessions</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <?php include('../includes/header.php'); ?>

    <div class="container">
        <h2>Upcoming Training Sessions</h2>
        <?php if ($upcoming_result->num_rows > 0): ?>
            <ul class="session-list">
                <?php while($row = $upcoming_result->fetch_assoc()): ?>
                    <li class="session-card">
                        <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                        <span><?= $row['date'] . ' at ' . substr($row['time'], 0, 5) ?></span><br>
                        <span>Route: <?= htmlspecialchars($row['route'] ?: 'Not specified') ?></span><br>
                        <span>Grade: <?= htmlspecialchars($row['grade']) ?></span><br>
                        <small>Instructor: <?= htmlspecialchars($row['instructor_name']) ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No upcoming sessions found.</p>
        <?php endif; ?>

        <hr>

        <h2>Completed Sessions & Reviews</h2>
        <?php if ($completed_result && $completed_result->num_rows > 0): ?>
            <ul class="session-list">
                <?php
                $last_session_id = null;
                while ($row = $completed_result->fetch_assoc()):
                    $is_new_session = $row['session_id'] !== $last_session_id;
                    if ($is_new_session && $last_session_id !== null) echo "</li>";
                    if ($is_new_session):
                ?>
                    <li class="session-card">
                        <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                        <span><?= $row['date'] . ' at ' . substr($row['time'], 0, 5) ?></span><br>
                        <span>Route: <?= htmlspecialchars($row['route'] ?: 'Not specified') ?></span><br>
                        <span>Grade: <?= htmlspecialchars($row['grade']) ?></span><br>
                        <small>Instructor: <?= htmlspecialchars($row['instructor_name']) ?></small><br><br>
                        <em>Reviews:</em><br>
                <?php endif; ?>

                <?php if (!empty($row['rating'])): ?>
                    <div class="review">
                        ⭐ <?= intval($row['rating']) ?>/5 — <?= htmlspecialchars($row['comment']) ?><br>
                        <small>- <?= htmlspecialchars($row['reviewer_name']) ?> on <?= date('F j, Y', strtotime($row['created_at'])) ?></small><br>
                    </div>
                <?php endif; ?>

                <?php $last_session_id = $row['session_id']; endwhile;
                if ($last_session_id !== null) echo "</li>"; ?>
            </ul>
        <?php else: ?>
            <p>No completed sessions or reviews found.</p>
        <?php endif; ?>
    </div>

    <?php include('../includes/footer.php'); ?>
</div>
</body>
</html>

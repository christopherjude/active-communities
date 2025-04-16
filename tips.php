<?php
session_start();
require_once 'includes/db.php';

$query = "
    SELECT it.tip, it.created_at, u.name 
    FROM instructor_tips it
    JOIN users u ON it.user_id = u.id
    ORDER BY it.created_at DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Instructor Tips</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- match other pages -->
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/header.php'; ?>

        <div class="container">
            <h2>Instructor Tips</h2>

            <?php if (mysqli_num_rows($result) === 0): ?>
                <p>No tips have been posted yet.</p>
            <?php else: ?>
                <ul class="tip-list">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <li class="tip-card">
                            <p class="tip-text"><?= nl2br(htmlspecialchars($row['tip'])) ?></p>
                            <div class="tip-meta">
                                Posted by <strong><?= htmlspecialchars($row['name']) ?></strong>
                                on <?= date('F j, Y, g:i a', strtotime($row['created_at'])) ?>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>

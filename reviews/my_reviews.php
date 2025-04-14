<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registered') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
$user_id = $_SESSION['user_id'];
$message = '';

// ✅ Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session_id'], $_POST['rating'], $_POST['comment'])) {
    $session_id = intval($_POST['session_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    // Check for existing review
    $check = $conn->prepare("SELECT id FROM reviews WHERE reviewer_id = ? AND session_id = ?");
    $check->bind_param("ii", $user_id, $session_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "You've already reviewed this session.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reviews (reviewer_id, session_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $session_id, $rating, $comment);
        if ($stmt->execute()) {
            $message = "Review submitted successfully.";
        } else {
            $message = "Something went wrong.";
        }
    }
}

// ✅ Sessions user has attended but not reviewed
$to_review = $conn->prepare("
    SELECT ts.id, ts.title, ts.date, ts.time 
    FROM bookings b
    JOIN training_sessions ts ON b.session_id = ts.id
    WHERE b.user_id = ? AND CONCAT(ts.date, ' ', ts.time) < NOW()
    AND ts.id NOT IN (
        SELECT session_id FROM reviews WHERE reviewer_id = ?
    )
    ORDER BY ts.date DESC
");
$to_review->bind_param("ii", $user_id, $user_id);
$to_review->execute();
$pending_result = $to_review->get_result();

// ✅ User’s past reviews
$reviews = $conn->prepare("
    SELECT r.id, r.rating, r.comment, r.created_at, ts.title, ts.date, ts.time
    FROM reviews r
    JOIN training_sessions ts ON r.session_id = ts.id
    WHERE r.reviewer_id = ?
    ORDER BY r.created_at DESC
");
$reviews->bind_param("i", $user_id);
$reviews->execute();
$reviews_result = $reviews->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Reviews</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <?php include('../includes/header.php'); ?>
    <div class="container">
        <h2>My Session Reviews</h2>
        <?php if ($message): ?>
            <p style="color: green; font-weight: bold;"><?php echo $message; ?></p>
        <?php endif; ?>

        <div class="review-flex">
            <!-- LEFT: Pending Sessions -->
            <div class="review-panel">
                <h3>Sessions to Review</h3>
                <?php if ($pending_result->num_rows > 0): ?>
                    <?php while ($row = $pending_result->fetch_assoc()): ?>
                        <div class="session-card">
                            <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                            <small><?php echo $row['date'] . " at " . substr($row['time'], 0, 5); ?></small>
                            <form method="POST">
                                <input type="hidden" name="session_id" value="<?php echo $row['id']; ?>">
                                <label>Rating (1–5)</label>
                                <input type="number" name="rating" min="1" max="5" required>
                                <label>Comment</label>
                                <textarea name="comment" rows="3" required></textarea>
                                <button type="submit">Submit Review</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No sessions left to review.</p>
                <?php endif; ?>
            </div>

            <!-- RIGHT: Submitted Reviews -->
            <div class="review-panel">
                <h3>Your Reviews</h3>
                <?php if ($reviews_result->num_rows > 0): ?>
                    <?php while ($r = $reviews_result->fetch_assoc()): ?>
                        <div class="session-card">
                            <strong><?php echo htmlspecialchars($r['title']); ?></strong><br>
                            <small><?php echo $r['date'] . " at " . substr($r['time'], 0, 5); ?></small>
                            <div class="review-overlay">
                                <p>⭐ <?php echo $r['rating']; ?>/5</p>
                                <em><?php echo nl2br(htmlspecialchars($r['comment'])); ?></em>
                                <div class="review-actions">
                                    <span>On <?php echo date('M j, Y', strtotime($r['created_at'])); ?></span>
                                    <span><a href="#">Edit</a> | <a href="#">Delete</a></span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>You haven’t submitted any reviews yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registered') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
$user_id = $_SESSION['user_id'];
$message = '';

// ✅ Handle new review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructor_id'], $_POST['rating'], $_POST['comment'])) {
    $instructor_id = intval($_POST['instructor_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    // Check if already reviewed
    $check = $conn->prepare("SELECT id FROM reviews WHERE reviewer_id = ? AND instructor_id = ?");
    $check->bind_param("ii", $user_id, $instructor_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "You've already reviewed this instructor.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reviews (reviewer_id, instructor_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $instructor_id, $rating, $comment);
        if ($stmt->execute()) {
            $message = "Review submitted successfully.";
        } else {
            $message = "Something went wrong.";
        }
    }
}

// ✅ Fetch user's existing reviews
$my_reviews = $conn->prepare("
    SELECT r.rating, r.comment, r.created_at, u.name AS instructor_name
    FROM reviews r
    JOIN users u ON r.instructor_id = u.id
    WHERE r.reviewer_id = ?
    ORDER BY r.created_at DESC
");
$my_reviews->bind_param("i", $user_id);
$my_reviews->execute();
$reviews_result = $my_reviews->get_result();

// ✅ Fetch past sessions for review
$past_sessions = $conn->prepare("
    SELECT DISTINCT u.id, u.name
    FROM bookings b
    JOIN training_sessions ts ON b.session_id = ts.id
    JOIN users u ON ts.instructor_id = u.id
    WHERE b.user_id = ? AND ts.date < CURDATE()
    AND u.id NOT IN (
        SELECT instructor_id FROM reviews WHERE reviewer_id = ?
    )
");
$past_sessions->bind_param("ii", $user_id, $user_id);
$past_sessions->execute();
$to_review = $past_sessions->get_result();
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
        <h2>My Reviews</h2>

        <?php if ($message): ?>
            <p style="color: green; font-weight: bold;"><?php echo $message; ?></p>
        <?php endif; ?>

        <!-- ✅ Write new review -->
        <?php if ($to_review->num_rows > 0): ?>
            <h3>Leave a New Review</h3>
            <form method="POST" class="session-form">
                <label for="instructor_id">Instructor:</label>
                <select name="instructor_id" required>
                    <?php while ($row = $to_review->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="rating">Rating (1-5):</label>
                <input type="number" name="rating" min="1" max="5" required>

                <label for="comment">Comment:</label>
                <textarea name="comment" rows="4" required></textarea>

                <button type="submit">Submit Review</button>
            </form>
        <?php else: ?>
            <p>You have no new instructors to review.</p>
        <?php endif; ?>

        <hr>

        <!-- ✅ Show submitted reviews -->
        <h3>Your Submitted Reviews</h3>
        <?php if ($reviews_result->num_rows > 0): ?>
            <ul class="session-list">
                <?php while ($r = $reviews_result->fetch_assoc()): ?>
                    <li class="session-card">
                        <strong><?php echo htmlspecialchars($r['instructor_name']); ?></strong><br>
                        Rating: <?php echo $r['rating']; ?>/5<br>
                        <em><?php echo nl2br(htmlspecialchars($r['comment'])); ?></em><br>
                        <small>Submitted on: <?php echo date("F j, Y", strtotime($r['created_at'])); ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>You haven’t submitted any reviews yet.</p>
        <?php endif; ?>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>
</body>
</html>

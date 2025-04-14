<?php
session_start();
require_once '../includes/db.php';

// Fetch all upcoming sessions
$sql = "SELECT ts.*, u.name AS instructor_name
        FROM training_sessions ts
        JOIN users u ON ts.instructor_id = u.id
        ORDER BY ts.date ASC, ts.time ASC";


$result = $conn->query($sql);
$booking_msg = '';

// Handle booking submission
if (isset($_POST['book_session_id']) && $_SESSION['role'] === 'registered') {
    $session_id = intval($_POST['book_session_id']);
    $user_id = $_SESSION['user_id'];

    // Check if already booked
    $check = $conn->prepare("SELECT 1 FROM bookings WHERE user_id = ? AND session_id = ?");
    $check->bind_param("ii", $user_id, $session_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $booking_msg = "You’ve already booked this session.";
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, session_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $session_id);
        if ($stmt->execute()) {
            $booking_msg = "Booking successful!";
        } else {
            $booking_msg = "Something went wrong. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Training Sessions</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('../includes/header.php'); ?>

        <div class="container">
            <h2>Upcoming Training Sessions</h2>
            <?php if (!empty($booking_msg)): ?>
                <p style="color: green; font-weight: bold;"><?php echo $booking_msg; ?></p>
            <?php endif; ?>


            <?php if ($result->num_rows > 0): ?>
                <ul class="session-list">
                    <?php while($row = $result->fetch_assoc()): ?>
                        <li class="session-card">
                            <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                            <span><?php echo $row['date'] . ' at ' . substr($row['time'], 0, 5); ?></span><br>
                            <span>Route: <?php echo htmlspecialchars($row['route'] ?: 'Not specified'); ?></span><br>
                            <span>Grade: <?php echo htmlspecialchars($row['grade']); ?></span><br>
                            <small>Instructor: <?php echo htmlspecialchars($row['instructor_name']); ?></small>

                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'registered'): ?>
                                <form method="post" style="margin-top: 0.5rem;">
                                    <input type="hidden" name="book_session_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit">Book Session</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No training sessions found.</p>
            <?php endif; ?>
        </div>

        <?php include('../includes/footer.php'); ?> 
    </div>
</body>
</html>

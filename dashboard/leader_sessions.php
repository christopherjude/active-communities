<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header("Location: ../index.php");
    exit();
}
require_once '../includes/db.php';

// Handle session creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $route = $_POST['route'];
    $grade = $_POST['grade'];
    $instructor_id = $_POST['instructor_id'];

    if (empty($instructor_id)) {
        die("Instructor must be selected.");
    }

    $stmt = $conn->prepare("INSERT INTO training_sessions (title, date, time, route, grade, instructor_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssii", $title, $date, $time, $route, $grade, $instructor_id, $_SESSION['user_id']);
    $stmt->execute();
}

$sessions = $conn->query("
    SELECT ts.*, u.name AS instructor_name
    FROM training_sessions ts
    JOIN users u ON ts.instructor_id = u.id"
    // WHERE ts.created_by = " . intval($_SESSION['user_id'])
);


$instructors = $conn->query("
    SELECT u.id, u.name 
    FROM instructors i
    JOIN users u ON i.user_id = u.id
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Training Sessions</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('../includes/header.php'); ?>
        <div class="container">
    <h2>Manage Training Sessions</h2>
    <div class="flex-layout">
        <!-- Left: Session List -->
        <div class="panel left">
            <h3>Sessions</h3>
            <ul class="session-list">
                <?php while($row = $sessions->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                        <?php echo $row['date'] . " at " . substr($row['time'], 0, 5); ?><br>
                        <small><?php echo htmlspecialchars($row['route']) . " | " . $row['grade']; ?></small>
                        <small>Instructor: <?php echo htmlspecialchars($row['instructor_name']); ?></small><br>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <!-- Right: Create Form -->
        <div class="panel right">
            <h3>Create New Session</h3>
            <form method="post" class="session-form">
                <input type="text" name="title" placeholder="Session Title" required>
                <input type="date" name="date" required>
                <input type="time" name="time" required>
                <input type="text" name="route" placeholder="Route (optional)">
                <input type="text" name="grade" placeholder="Grade (Beginner, Advanced)" required>
                <select name="instructor_id" required>
                    <option value="">-- Select Instructor --</option>
                    <?php while ($inst = $instructors->fetch_assoc()): ?>
                        <option value="<?php echo $inst['id']; ?>"><?php echo htmlspecialchars($inst['name']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Create Session</button>
            </form>
        </div>
    </div>
</div>

        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>

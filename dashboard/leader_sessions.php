<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header("Location: ../index.php");
    exit();
}
require_once '../includes/db.php';

// --- Handle session creation and updating ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $route = $_POST['route'];
    $grade = $_POST['grade'];
    $instructor_id = $_POST['instructor_id'];

    if (empty($instructor_id)) {
        die("Instructor must be selected.");
    }

    if (isset($_POST['update_session_id'])) {
        $update_id = intval($_POST['update_session_id']);
        $stmt = $conn->prepare("UPDATE training_sessions SET title=?, date=?, time=?, route=?, grade=?, instructor_id=? WHERE id=?");
        $stmt->bind_param("ssssssi", $title, $date, $time, $route, $grade, $instructor_id, $update_id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO training_sessions (title, date, time, route, grade, instructor_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssii", $title, $date, $time, $route, $grade, $instructor_id, $_SESSION['user_id']);
        $stmt->execute();
    }

    header("Location: leader_sessions.php");
    exit();
}

// --- Handle deletion ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_session_id'])) {
    $delete_id = intval($_POST['delete_session_id']);
    $stmt = $conn->prepare("DELETE FROM training_sessions WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: leader_sessions.php");
    exit();
}

// --- Get sessions ---
$sessions = $conn->query("
    SELECT ts.*, u.name AS instructor_name
    FROM training_sessions ts
    JOIN users u ON ts.instructor_id = u.id
");

// --- Get instructors ---
$instructors = $conn->query("
    SELECT u.id, u.name 
    FROM instructors i
    JOIN users u ON i.user_id = u.id
");

// --- Edit mode ---
$editing = false;
$edit_data = null;
if (isset($_GET['edit_session_id'])) {
    $editing = true;
    $edit_id = intval($_GET['edit_session_id']);
    $edit_stmt = $conn->prepare("SELECT * FROM training_sessions WHERE id = ?");
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_data = $edit_stmt->get_result()->fetch_assoc();
}
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
                    <?php while ($row = $sessions->fetch_assoc()): ?>
                        <?php
                        $datetime = strtotime($row['date'] . ' ' . $row['time']);
                        $is_past = $datetime < time();
                        ?>
                        <li style="<?php echo $is_past ? 'opacity: 0.6;' : ''; ?>">
                            <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                            <?php echo $row['date'] . " at " . substr($row['time'], 0, 5); ?><br>
                            <small><?php echo htmlspecialchars($row['route']) . " | " . $row['grade']; ?></small><br>
                            <small>Instructor: <?php echo htmlspecialchars($row['instructor_name']); ?></small><br>
                            <small><em><?php echo $is_past ? 'Finished' : 'Upcoming'; ?></em></small>

                            <?php if (!$is_past): ?>
                                <div style="margin-top: 0.5rem;">
                                    <form method="GET" style="display: inline;">
                                        <input type="hidden" name="edit_session_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="edit-button">Edit</button>
                                    </form>

                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="delete_session_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this session?');">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <!-- Right: Create/Edit Form -->
            <div class="panel right">
                <h3><?php echo $editing ? 'Edit Session' : 'Create New Session'; ?></h3>
                <form method="post" class="session-form">
                    <?php if ($editing): ?>
                        <input type="hidden" name="update_session_id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>

                    <input type="text" name="title" placeholder="Session Title" required value="<?php echo $editing ? htmlspecialchars($edit_data['title']) : ''; ?>">
                    <input type="date" name="date" required value="<?php echo $editing ? $edit_data['date'] : ''; ?>">
                    <input type="time" name="time" required value="<?php echo $editing ? $edit_data['time'] : ''; ?>">
                    <input type="text" name="route" placeholder="Route (optional)" value="<?php echo $editing ? htmlspecialchars($edit_data['route']) : ''; ?>">
                    <input type="text" name="grade" placeholder="Grade (Beginner, Advanced)" required value="<?php echo $editing ? htmlspecialchars($edit_data['grade']) : ''; ?>">

                    <select name="instructor_id" required>
                        <option value="">-- Select Instructor --</option>
                        <?php
                        $instructors->data_seek(0); // Reset pointer
                        while ($inst = $instructors->fetch_assoc()):
                        ?>
                            <option value="<?php echo $inst['id']; ?>"
                                <?php echo ($editing && $inst['id'] == $edit_data['instructor_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($inst['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <button type="submit"><?php echo $editing ? 'Update Session' : 'Create Session'; ?></button>
                </form>
            </div>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>
</body>
</html>

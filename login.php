<?php
require_once 'includes/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password_input = $_POST['password'];

    $sql = "SELECT id, name, password FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($password_input, $result['password'])) {
        $user_id = $result['id'];
        $_SESSION['user_id'] = $user_id;
        $_SESSION['name'] = $result['name'];

        // ✅ Now check which role the user belongs to
        $role = 'registered'; // default

        $check_leader = $conn->prepare("SELECT 1 FROM leaders WHERE user_id = ?");
        $check_leader->bind_param("i", $user_id);
        $check_leader->execute();
        if ($check_leader->get_result()->num_rows > 0) {
            $role = 'leader';
        } else {
            $check_instructor = $conn->prepare("SELECT 1 FROM instructors WHERE user_id = ?");
            $check_instructor->bind_param("i", $user_id);
            $check_instructor->execute();
            if ($check_instructor->get_result()->num_rows > 0) {
                $role = 'instructor';
            }
        }

        $_SESSION['role'] = $role;

        // ✅ Redirect based on role
        if ($role === 'leader') {
            header("Location: dashboard/leader_home.php");
        } elseif ($role === 'instructor') {
            header("Location: dashboard/instructor_home.php");
        } else {
            header("Location: dashboard/user_home.php");
        }
        exit();

    } else {
        echo "Invalid login";
    }
}
?>

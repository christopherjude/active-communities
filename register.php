<?php
require_once 'includes/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        $conn->query("INSERT INTO registered_users (user_id) VALUES ($user_id)");

        header("Location: index.php");
    } else {
        echo "Error: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Active Communities</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('includes/header.php'); ?>
        <div class="container">
            <div class="title-box">
                <h2>Create an Account</h2>
                <p>Join the movement! Register now to book sessions, apply as an instructor, or just enjoy the ride.</p>
            </div>

            <div class="login-box">
                <form method="POST" action="register.php" class="login-form">
                    <label for="name">Name:</label>
                    <input type="text" name="name" id="name" required>

                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>

                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>

                    <div class="login-links-split">
                        <a href="index.php">Already have an account?</a>
                        <!-- <a href="#">Need help?</a> -->
                    </div>

                    <button type="submit">Register</button>
                </form>
            </div>
        </div>
        <?php include('includes/footer.php'); ?>
    </div>
</body>
</html>

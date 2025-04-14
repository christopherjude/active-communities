<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Active Communities</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include('includes/header.php'); ?>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="container">
                <div class="title-box">
                    <h2>Welcome to Active Communities</h2>
                    <p>Join a growing cycling community. Book sessions, connect with instructors, and ride your way to better health.</p>
                </div>

                <div class="login-box">
                    <form method="POST" action="login.php" class="login-form">
                        <label for="email">Email:</label>
                        <input type="email" name="email" required>

                        <label for="password">Password:</label>
                        <input type="password" name="password" required>

                        <div class="login-links-split">
                            <a href="register.php">Create an account</a>
                            <a href="#">Forgot password?</a>
                        </div>

                        <button type="submit">Login</button>
                    </form>
                </div>
            </div>
            <?php else: ?>
                <h2>Welcome, <?php echo $_SESSION['name']; ?>!</h2>
                <p><a href="dashboard/user_home.php">Go to your dashboard</a> to manage your bookings and profile.</p>
            <?php endif; ?>
        <?php include('includes/footer.php'); ?>
    </div>
</body>
</html>



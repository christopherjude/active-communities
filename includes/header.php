<!DOCTYPE html>
<html>
<head>
    <title>Active Communities</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
    <header>
        <h1>Active Communities</h1>
        <p>Get involved. Get fit. Get cycling.</p>
    </header>
    <nav>
        <?php
        $homeLink = "/active-communities";
        $role = $_SESSION['role'] ?? null;

        if (isset($_SESSION['user_id'])) {
            // Determine homepage by role
            switch ($role) {
                case 'leader':
                    $homeLink = "/active-communities/dashboard/leader_home.php";
                    break;
                case 'instructor':
                    $homeLink = "/active-communities/dashboard/instructor_home.php";
                    break;
                case 'registered':
                default:
                    $homeLink = "/active-communities/dashboard/user_home.php";
                    break;
            }
        }
        ?>

        <!-- Universal Links -->
        <a href="<?php echo $homeLink; ?>">Home</a>
        <a href="/active-communities/sessions/list.php">Training Sessions</a>
        <a href="/active-communities/contact/form.php">Contact Us</a>

        <!-- Role-Specific Links -->
        <?php if ($role === 'leader'): ?>
            <a href="/active-communities/dashboard/leader_sessions.php">Manage Sessions</a>
            <a href="/active-communities/dashboard/instructor_approvals.php">Approve Instructors</a>
            <a href="/active-communities/dashboard/contact_inbox.php">Contact Inbox</a>
        <?php elseif ($role === 'instructor'): ?>
            <a href="/active-communities/sessions/instructor_sessions.php">My Sessions</a>
        <?php elseif ($role === 'registered'): ?>
            <a href="/active-communities/reviews/my_reviews.php">My Reviews</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/active-communities/logout.php">Logout</a>
        <?php endif; ?>
    </nav>

<?php
session_start();
require_once '../includes/db.php';

$sql = "SELECT u.name, i.experience, i.profile_bio, i.profile_picture 
        FROM instructors i
        JOIN users u ON i.user_id = u.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Our Instructors</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .instructor-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .instructor-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center;
        }

        .instructor-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .instructor-card h3 {
            margin: 0.5rem 0;
            color: #111827;
        }

        .instructor-card p {
            font-size: 0.9rem;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include('../includes/header.php'); ?>
        <div class="container">
            <h2>Meet Our Instructors</h2>
            <?php if ($result->num_rows > 0): ?>
                <div class="instructor-list">
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="instructor-card">
                            <?php
                            $pic = !empty($row['profile_picture']) ? $row['profile_picture'] : 'uploads/instructors/placeholder.jpg';
                            ?>
                            <img src="../<?php echo htmlspecialchars($pic); ?>" alt="Instructor Profile">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p><strong>Experience:</strong> <?php echo htmlspecialchars($row['experience']); ?></p>
                            <p><?php echo nl2br(htmlspecialchars($row['profile_bio'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No instructors found.</p>
            <?php endif; ?>
        </div>
        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>

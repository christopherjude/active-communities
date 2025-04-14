<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
$user_id = $_SESSION['user_id'];

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $experience = $_POST['experience'];
    $bio = $_POST['bio'];

    // Handle image upload
    $picture_path = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/instructors/";
        $filename = basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . time() . "_" . $filename;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $picture_path = substr($target_file, 3); // Remove "../" for web path
        }
    }

    if ($picture_path) {
        $stmt = $conn->prepare("UPDATE instructors SET experience=?, profile_bio=?, profile_picture=? WHERE user_id=?");
        $stmt->bind_param("sssi", $experience, $bio, $picture_path, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE instructors SET experience=?, profile_bio=? WHERE user_id=?");
        $stmt->bind_param("ssi", $experience, $bio, $user_id);
    }

    if ($stmt->execute()) {
        $success = "Profile updated successfully.";
    } else {
        $error = "Something went wrong.";
    }
}

// Fetch profile data
$stmt = $conn->prepare("SELECT experience, profile_bio, profile_picture FROM instructors WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

$experience = $profile['experience'] ?? '';
$bio = $profile['profile_bio'] ?? '';
$picture = !empty($profile['profile_picture']) ? $profile['profile_picture'] : 'uploads/instructors/placeholder.jpg';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Instructor Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .profile-form {
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include('../includes/header.php'); ?>

    <div class="container">
        <h2>Edit Your Instructor Profile</h2>

        <?php if ($success): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php elseif ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <div class="profile-form">
            <?php $picture = !empty($profile['profile_picture']) ? $profile['profile_picture'] : 'uploads/instructors/placeholder.png'; ?>
            <img src="../<?php echo htmlspecialchars($picture); ?>" class="profile-img" alt="Profile Picture">

            <form method="post" enctype="multipart/form-data">
                <label for="experience">Experience:</label>
                <input type="text" name="experience" id="experience" value="<?php echo htmlspecialchars($experience); ?>" required>

                <label for="bio">Profile Bio:</label>
                <textarea name="bio" id="bio" rows="4" required><?php echo htmlspecialchars($bio); ?></textarea>

                <label for="profile_picture">Profile Picture:</label>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*">

                <button type="submit">Save Profile</button>
            </form>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
</div>
</body>
</html>

<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    // Validate inputs
    if (empty($name) || empty($email)) {
        header("Location: profil_citizen.php?error=empty");
        exit();
    }
    
    // Check if passwords match (if provided)
    if (!empty($new_password) && $new_password !== $confirm_password) {
        header("Location: profil_citizen.php?error=password");
        exit();
    }
    
    // Check if email is already used by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        header("Location: profil_citizen.php?error=email");
        exit();
    }
    
    // Get current user data
    $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_user = $stmt->fetch();
    $profile_photo = $current_user['profile_photo'];
    
    // Handle profile photo upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        
        if (!in_array($file['type'], $allowed_types)) {
            header("Location: profil_citizen.php?error=type");
            exit();
        }
        
        // Create uploads/profiles directory if it doesn't exist
        $upload_dir = 'uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Delete old photo if exists
        if (!empty($profile_photo) && file_exists($upload_dir . $profile_photo)) {
            unlink($upload_dir . $profile_photo);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = md5(uniqid() . time()) . '.' . $extension;
        $destination = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $profile_photo = $new_filename;
        } else {
            header("Location: profil_citizen.php?error=upload");
            exit();
        }
    }
    
    // Update user data
    try {
        if (!empty($new_password)) {
            // Update with new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, profile_photo = ? WHERE id = ?");
            $stmt->execute([$name, $email, $hashed_password, $profile_photo, $user_id]);
        } else {
            // Update without changing password
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, profile_photo = ? WHERE id = ?");
            $stmt->execute([$name, $email, $profile_photo, $user_id]);
        }
        
        header("Location: profil_citizen.php?success=1");
        exit();
        
    } catch (PDOException $e) {
        error_log("Error updating profile: " . $e->getMessage());
        header("Location: profil_citizen.php?error=database");
        exit();
    }
} else {
    header("Location: profil_citizen.php");
    exit();
}

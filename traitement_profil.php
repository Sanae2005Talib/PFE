<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'association') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $assoc_name = $_POST['association_name'];
    $user_name = $_POST['user_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $new_password = $_POST['new_password'];

    try {
        $pdo->beginTransaction();

        // 1. Update User table
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt1 = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt1->execute([$user_name, $email, $hashed_password, $user_id]);
        } else {
            $stmt1 = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt1->execute([$user_name, $email, $user_id]);
        }

        // 2. Handle Logo Upload
        $logo_name = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['logo']['tmp_name'];
            $fileName = $_FILES['logo']['name'];
            $fileSize = $_FILES['logo']['size'];
            $fileType = $_FILES['logo']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = './uploads/logos/';
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $logo_name = $newFileName;
                    
                    // Delete old logo if exists
                    $stmt_old = $pdo->prepare("SELECT logo FROM associations WHERE user_id = ?");
                    $stmt_old->execute([$user_id]);
                    $old_logo = $stmt_old->fetchColumn();
                    if ($old_logo && file_exists($uploadFileDir . $old_logo)) {
                        unlink($uploadFileDir . $old_logo);
                    }
                } else {
                    throw new Exception("upload_failed");
                }
            } else {
                throw new Exception("invalid_type");
            }
        }

        // 3. Update Association table
        if ($logo_name) {
            $stmt2 = $pdo->prepare("UPDATE associations SET association_name = ?, phone = ?, address = ?, logo = ? WHERE user_id = ?");
            $stmt2->execute([$assoc_name, $phone, $address, $logo_name, $user_id]);
        } else {
            $stmt2 = $pdo->prepare("UPDATE associations SET association_name = ?, phone = ?, address = ? WHERE user_id = ?");
            $stmt2->execute([$assoc_name, $phone, $address, $user_id]);
        }

        $pdo->commit();
        header("Location: profil.php?success=1");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'general';
        if ($e->getMessage() === 'upload_failed') $error = 'upload';
        if ($e->getMessage() === 'invalid_type') $error = 'type';
        header("Location: profil.php?error=" . $error);
        exit();
    }
}
?>

<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $assoc_id = $_GET['id'];
    
    try {
        $pdo->beginTransaction();

        // Get user_id before deleting
        $stmt = $pdo->prepare("SELECT user_id FROM associations WHERE id = ?");
        $stmt->execute([$assoc_id]);
        $user_id = $stmt->fetchColumn();

        if ($user_id) {
            // Delete association (cascade might handle it, but better safe)
            $pdo->prepare("DELETE FROM associations WHERE id = ?")->execute([$assoc_id]);
            // Delete user
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        }

        $pdo->commit();
        header("Location: admin_dashboard.php?msg=rejected");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur de rejet : " . $e->getMessage());
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
?>

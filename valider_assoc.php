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
        $stmt = $pdo->prepare("UPDATE associations SET is_validated = 1 WHERE id = ?");
        $stmt->execute([$assoc_id]);
        
        header("Location: admin_dashboard.php?msg=validated");
        exit();
    } catch (Exception $e) {
        die("Erreur de validation : " . $e->getMessage());
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
?>

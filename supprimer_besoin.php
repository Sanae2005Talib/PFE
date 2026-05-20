<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'association') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Vérifier que le besoin appartient bien à l'association connectée
    $stmt_check = $pdo->prepare("
        SELECT n.id FROM needs n
        JOIN associations a ON n.association_id = a.id
        WHERE n.id = ? AND a.user_id = ?
    ");
    $stmt_check->execute([$id, $_SESSION['user_id']]);
    $besoin = $stmt_check->fetch();

    if (!$besoin) {
        header("Location: association_dashboard.php?error=acces_refuse");
        exit();
    }

    // Suppression sécurisée
    $stmt = $pdo->prepare("DELETE FROM needs WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        header("Location: association_dashboard.php?msg=deleted");
    } else {
        header("Location: association_dashboard.php?error=suppression_echouee");
    }
} else {
    header("Location: association_dashboard.php");
}
exit();
?>

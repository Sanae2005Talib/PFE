<?php
session_start();
include_once 'api/conn.php';

// Sécurité: Ila l-user machi association, rj3o l-login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'association') {
    header("Location: login.php");
    exit();
}

// 1. Njibou l-ID dial l'association mn table 'associations' b-mosa3adat 'user_id'
$stmt = $pdo->prepare("SELECT id FROM associations WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$assoc = $stmt->fetch();
$assoc_id = $assoc['id'];

// 2. Njibou les types de dons pour le menu déroulant
$types = $pdo->query("SELECT * FROM donation_types")->fetchAll();

// 3. Njibou les besoins li déja lo7at had l-jam3iya
$stmt_needs = $pdo->prepare("SELECT n.*, t.name as type_name FROM needs n 
                             JOIN donation_types t ON n.donation_type_id = t.id 
                             WHERE n.association_id = ? ");
$stmt_needs->execute([$assoc_id]);
$my_needs = $stmt_needs->fetchAll();
?>
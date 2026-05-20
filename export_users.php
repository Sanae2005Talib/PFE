<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Récupérer tous les utilisateurs
$query = "SELECT u.*, 
          (SELECT association_name FROM associations WHERE user_id = u.id LIMIT 1) as association_name
          FROM users u
          ORDER BY u.id DESC";

$stmt = $pdo->query($query);
$users = $stmt->fetchAll();

// Nom du fichier avec date
$filename = "utilisateurs_" . date('Y-m-d_H-i-s') . ".csv";

// Headers pour téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Créer le fichier CSV
$output = fopen('php://output', 'w');

// BOM pour UTF-8 (pour Excel)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes du CSV
fputcsv($output, [
    'ID',
    'Nom',
    'Email',
    'Rôle',
    'Association',
    'Photo Profil'
], ';');

// Données
foreach ($users as $user) {
    $role = '';
    if ($user['role'] === 'citizen') $role = 'Donateur';
    elseif ($user['role'] === 'association') $role = 'Association';
    else $role = 'Admin';
    
    fputcsv($output, [
        $user['id'],
        $user['name'],
        $user['email'],
        $role,
        $user['association_name'] ?? '-',
        $user['profile_photo'] ? 'Oui' : 'Non'
    ], ';');
}

fclose($output);
exit();

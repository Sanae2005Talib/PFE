<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Récupérer toutes les associations
$query = "SELECT a.*, u.email, r.name as region_name,
          (SELECT COUNT(*) FROM needs WHERE association_id = a.id) as needs_count
          FROM associations a 
          JOIN users u ON a.user_id = u.id 
          LEFT JOIN regions r ON a.region_id = r.id
          ORDER BY a.id DESC";

$stmt = $pdo->query($query);
$associations = $stmt->fetchAll();

// Nom du fichier avec date
$filename = "associations_" . date('Y-m-d_H-i-s') . ".csv";

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
    'Nom Association',
    'Email',
    'Téléphone',
    'Adresse',
    'Région',
    'Statut',
    'Nombre de Besoins',
    'Description'
], ';');

// Données
foreach ($associations as $assoc) {
    fputcsv($output, [
        $assoc['id'],
        $assoc['association_name'],
        $assoc['email'],
        $assoc['phone'] ?? 'N/A',
        $assoc['address'] ?? 'N/A',
        $assoc['region_name'] ?? 'N/A',
        $assoc['is_validated'] ? 'Validée' : 'En attente',
        $assoc['needs_count'],
        $assoc['description'] ?? 'N/A'
    ], ';');
}

fclose($output);
exit();

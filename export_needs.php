<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Récupérer tous les besoins
$query = "SELECT n.*, a.association_name, dt.name as type_name
          FROM needs n
          JOIN associations a ON n.association_id = a.id
          JOIN donation_types dt ON n.donation_type_id = dt.id
          ORDER BY n.id DESC";

$stmt = $pdo->query($query);
$needs = $stmt->fetchAll();

// Nom du fichier avec date
$filename = "besoins_" . date('Y-m-d_H-i-s') . ".csv";

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
    'Titre',
    'Association',
    'Type',
    'Description',
    'Quantité',
    'Localisation',
    'Date Limite',
    'Statut'
], ';');

// Données
foreach ($needs as $need) {
    fputcsv($output, [
        $need['id'],
        $need['title'],
        $need['association_name'],
        $need['type_name'],
        $need['description'],
        $need['quantity'] ?? 'N/A',
        $need['location'] ?? 'N/A',
        $need['deadline'] ?? 'N/A',
        $need['status'] === 'urgent' ? 'Urgent' : ($need['status'] === 'normal' ? 'Normal' : 'Satisfait')
    ], ';');
}

fclose($output);
exit();

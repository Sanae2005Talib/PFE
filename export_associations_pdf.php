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

$validated = array_filter($associations, fn($a) => $a['is_validated']);
$pending = count($associations) - count($validated);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Associations PDF</title>
    <style>
        @media print {
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #10b981;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #10b981;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            color: #666;
            margin: 5px 0;
        }
        .stats {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-around;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #10b981;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background: #f8fafc;
        }
        .status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .status.validated {
            background: #dcfce7;
            color: #16a34a;
        }
        .status.pending {
            background: #fef3c7;
            color: #d97706;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .print-btn:hover {
            background: #059669;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        📄 Imprimer / Sauvegarder PDF
    </button>

    <div class="header">
        <h1>🤝 Solidarité Connect</h1>
        <p>Liste des Associations</p>
        <p style="font-size: 12px;">Généré le <?= date('d/m/Y à H:i') ?></p>
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-value"><?= count($associations) ?></div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?= count($validated) ?></div>
            <div class="stat-label">Validées</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?= $pending ?></div>
            <div class="stat-label">En Attente</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom Association</th>
                <th>Email</th>
                <th>Région</th>
                <th>Téléphone</th>
                <th>Statut</th>
                <th>Besoins</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($associations as $assoc): ?>
            <tr>
                <td><strong>#<?= $assoc['id'] ?></strong></td>
                <td><?= htmlspecialchars($assoc['association_name']) ?></td>
                <td><?= htmlspecialchars($assoc['email']) ?></td>
                <td><?= htmlspecialchars($assoc['region_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($assoc['phone'] ?? 'N/A') ?></td>
                <td>
                    <span class="status <?= $assoc['is_validated'] ? 'validated' : 'pending' ?>">
                        <?= $assoc['is_validated'] ? '✓ Validée' : '⏱ En attente' ?>
                    </span>
                </td>
                <td><?= $assoc['needs_count'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>© <?= date('Y') ?> Solidarité Connect - Tous droits réservés</p>
        <p>Document généré automatiquement par le système</p>
    </div>
</body>
</html>

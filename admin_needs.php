<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Récupérer les filtres
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';
$type_filter = $_GET['type'] ?? 'all';

// Construire la requête
$query = "SELECT n.*, a.association_name, dt.name as type_name
          FROM needs n
          JOIN associations a ON n.association_id = a.id
          JOIN donation_types dt ON n.donation_type_id = dt.id
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (n.title LIKE :search OR a.association_name LIKE :search OR n.location LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($status_filter !== 'all') {
    $query .= " AND n.status = :status";
    $params[':status'] = $status_filter;
}

if ($type_filter !== 'all') {
    $query .= " AND n.donation_type_id = :type";
    $params[':type'] = $type_filter;
}

$query .= " ORDER BY n.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$needs = $stmt->fetchAll();

// Stats
$count_all = $pdo->query("SELECT COUNT(*) FROM needs")->fetchColumn();
$count_urgent = $pdo->query("SELECT COUNT(*) FROM needs WHERE status = 'urgent'")->fetchColumn();
$count_normal = $pdo->query("SELECT COUNT(*) FROM needs WHERE status = 'normal'")->fetchColumn();
$count_satisfied = $pdo->query("SELECT COUNT(*) FROM needs WHERE status = 'satisfied'")->fetchColumn();

// Types
$types = $pdo->query("SELECT * FROM donation_types ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Besoins - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; display: flex; min-height: 100vh; }

        /* Sidebar - Same as associations */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .brand-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        .brand-text { color: white; font-size: 1.25rem; font-weight: 800; }
        .brand-text span { color: #10b981; }
        .sidebar-nav { flex: 1; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
            font-weight: 500;
        }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: white; }
        .nav-item.active {
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        .nav-item i { width: 20px; text-align: center; font-size: 1.125rem; }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        /* Header */
        .page-header {
            margin-bottom: 2rem;
        }
        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        .page-subtitle {
            color: #64748b;
            font-size: 1rem;
        }

        /* Stats */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-box {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }
        .stat-box:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .stat-label { font-size: 0.875rem; color: #64748b; font-weight: 600; margin-bottom: 0.5rem; }
        .stat-value { font-size: 2rem; font-weight: 800; color: #1e293b; }

        /* Filters */
        .filters-bar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 0.875rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.9375rem;
        }
        .search-input:focus { outline: none; border-color: #10b981; }
        .filter-select {
            padding: 0.875rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.9375rem;
            background: white;
            cursor: pointer;
        }
        .filter-btn {
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .filter-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4); }

        /* Table */
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }
        .data-table thead {
            background: #f8fafc;
        }
        .data-table th {
            padding: 1rem 1rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .data-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.3s;
        }
        .data-table tbody tr:hover { background: #f8fafc; }
        .data-table td {
            padding: 1rem 1rem;
        }
        .need-title { 
            font-weight: 600; 
            color: #1e293b;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .status-badge.urgent { background: #fee2e2; color: #dc2626; }
        .status-badge.normal { background: #dbeafe; color: #0284c7; }
        .status-badge.satisfied { background: #dcfce7; color: #16a34a; }
        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            background: #dbeafe;
            color: #0284c7;
        }
        .action-btn:hover { background: #0284c7; color: white; }

        /* Export Button */
        .export-btn {
            padding: 0.875rem 1.5rem;
            background: white;
            color: #10b981;
            border: 2px solid #10b981;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .export-btn:hover {
            background: #10b981;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fas fa-heart"></i>
            </div>
            <div class="brand-text">Solidarité<span>Connect</span></div>
        </div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="admin_associations.php" class="nav-item">
                <i class="fas fa-building"></i>
                <span>Associations</span>
            </a>
            <a href="admin_needs.php" class="nav-item active">
                <i class="fas fa-clipboard-list"></i>
                <span>Besoins</span>
            </a>
            <a href="admin_users.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="admin_reports.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Rapports</span>
            </a>
        </nav>
        <a href="logout.php" class="nav-item" style="margin-top: auto; color: #ef4444;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Déconnexion</span>
        </a>
    </aside>

    <!-- Main -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Gestion des Besoins</h1>
            <p class="page-subtitle">Tous les besoins publiés par les associations</p>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-label">Total</div>
                <div class="stat-value"><?= $count_all ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Urgent</div>
                <div class="stat-value" style="color: #dc2626;"><?= $count_urgent ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Normal</div>
                <div class="stat-value" style="color: #0284c7;"><?= $count_normal ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Satisfait</div>
                <div class="stat-value" style="color: #10b981;"><?= $count_satisfied ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <input type="text" class="search-input" id="searchInput" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
            <select class="filter-select" id="statusFilter">
                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Tous statuts</option>
                <option value="urgent" <?= $status_filter === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                <option value="normal" <?= $status_filter === 'normal' ? 'selected' : '' ?>>Normal</option>
                <option value="satisfied" <?= $status_filter === 'satisfied' ? 'selected' : '' ?>>Satisfait</option>
            </select>
            <select class="filter-select" id="typeFilter">
                <option value="all" <?= $type_filter === 'all' ? 'selected' : '' ?>>Tous types</option>
                <?php foreach($types as $type): ?>
                    <option value="<?= $type['id'] ?>" <?= $type_filter == $type['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="filter-btn" onclick="applyFilters()">Filtrer</button>
            <button class="export-btn" onclick="window.location.href='export_needs.php'">
                <i class="fas fa-download"></i> Exporter
            </button>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Localisation</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($needs as $need): ?>
                        <tr>
                            <td><strong>#<?= $need['id'] ?></strong></td>
                            <td>
                                <div class="need-title" title="<?= htmlspecialchars($need['title']) ?>">
                                    <?= htmlspecialchars($need['title']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                                    <?= htmlspecialchars(substr($need['association_name'], 0, 25)) ?><?= strlen($need['association_name']) > 25 ? '...' : '' ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($need['type_name']) ?></td>
                            <td><?= htmlspecialchars($need['location'] ?? 'N/A') ?></td>
                            <td>
                                <span class="status-badge <?= $need['status'] ?>">
                                    <?php if($need['status'] === 'urgent'): ?>
                                        <i class="fas fa-fire"></i> Urgent
                                    <?php elseif($need['status'] === 'normal'): ?>
                                        <i class="fas fa-clock"></i> Normal
                                    <?php else: ?>
                                        <i class="fas fa-check"></i> Satisfait
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const type = document.getElementById('typeFilter').value;
            window.location.href = `admin_needs.php?search=${encodeURIComponent(search)}&status=${status}&type=${type}`;
        }
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') applyFilters();
        });
    </script>
</body>
</html>

<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Récupérer les filtres
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? 'all';

// Construire la requête
$query = "SELECT u.*, 
          (SELECT association_name FROM associations WHERE user_id = u.id LIMIT 1) as association_name
          FROM users u
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($role_filter !== 'all') {
    $query .= " AND u.role = :role";
    $params[':role'] = $role_filter;
}

$query .= " ORDER BY u.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Stats
$count_all = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$count_citizens = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'citizen'")->fetchColumn();
$count_associations = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'association'")->fetchColumn();
$count_admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; display: flex; min-height: 100vh; }

        /* Sidebar */
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
            min-width: 800px;
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
        .user-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 180px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            flex-shrink: 0;
        }
        .user-name { 
            font-weight: 600; 
            color: #1e293b;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .role-badge.citizen { background: #dbeafe; color: #0284c7; }
        .role-badge.association { background: #dcfce7; color: #16a34a; }
        .role-badge.admin { background: #fef3c7; color: #d97706; }

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
            <a href="admin_needs.php" class="nav-item">
                <i class="fas fa-clipboard-list"></i>
                <span>Besoins</span>
            </a>
            <a href="admin_users.php" class="nav-item active">
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
            <h1 class="page-title">Gestion des Utilisateurs</h1>
            <p class="page-subtitle">Tous les utilisateurs inscrits sur la plateforme</p>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-label">Total</div>
                <div class="stat-value"><?= $count_all ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Donateurs</div>
                <div class="stat-value" style="color: #0284c7;"><?= $count_citizens ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Associations</div>
                <div class="stat-value" style="color: #10b981;"><?= $count_associations ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Admins</div>
                <div class="stat-value" style="color: #d97706;"><?= $count_admins ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <input type="text" class="search-input" id="searchInput" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
            <select class="filter-select" id="roleFilter">
                <option value="all" <?= $role_filter === 'all' ? 'selected' : '' ?>>Tous les rôles</option>
                <option value="citizen" <?= $role_filter === 'citizen' ? 'selected' : '' ?>>Donateurs</option>
                <option value="association" <?= $role_filter === 'association' ? 'selected' : '' ?>>Associations</option>
                <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admins</option>
            </select>
            <button class="filter-btn" onclick="applyFilters()">Filtrer</button>
            <button class="export-btn" onclick="window.location.href='export_users.php'">
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
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Association</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><strong>#<?= $user['id'] ?></strong></td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </div>
                                    <div class="user-name" title="<?= htmlspecialchars($user['name']) ?>">
                                        <?= htmlspecialchars($user['name']) ?>
                                    </div>
                                </div>
                            </td>
                            <td title="<?= htmlspecialchars($user['email']) ?>">
                                <?= htmlspecialchars(substr($user['email'], 0, 30)) ?><?= strlen($user['email']) > 30 ? '...' : '' ?>
                            </td>
                            <td>
                                <span class="role-badge <?= $user['role'] ?>">
                                    <?php if($user['role'] === 'citizen'): ?>
                                        <i class="fas fa-user"></i> Donateur
                                    <?php elseif($user['role'] === 'association'): ?>
                                        <i class="fas fa-building"></i> Association
                                    <?php else: ?>
                                        <i class="fas fa-shield-alt"></i> Admin
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($user['association_name'] ?? '-') ?></td>
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
            const role = document.getElementById('roleFilter').value;
            window.location.href = `admin_users.php?search=${encodeURIComponent(search)}&role=${role}`;
        }
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') applyFilters();
        });
    </script>
</body>
</html>

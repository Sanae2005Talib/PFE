<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Récupérer les statistiques
$count_assoc = $pdo->query("SELECT COUNT(*) FROM associations")->fetchColumn();
$count_needs = $pdo->query("SELECT COUNT(*) FROM needs")->fetchColumn();
$count_pending = $pdo->query("SELECT COUNT(*) FROM associations WHERE is_validated = 0")->fetchColumn();
$count_citizens = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'citizen'")->fetchColumn();

// Récupérer toutes les associations avec leurs besoins
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$query = "SELECT a.*, u.email, u.name as user_name,
          (SELECT COUNT(*) FROM needs WHERE association_id = a.id) as needs_count
          FROM associations a 
          JOIN users u ON a.user_id = u.id 
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (a.association_name LIKE :search OR u.email LIKE :search OR a.address LIKE :search)";
}

if ($status_filter === 'validated') {
    $query .= " AND a.is_validated = 1";
} elseif ($status_filter === 'pending') {
    $query .= " AND a.is_validated = 0";
}

$query .= " ORDER BY a.id DESC";

$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$associations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Solidarité Connect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; display: flex; min-height: 100vh; }

        /* Header */
        .top-header {
            position: fixed;
            top: 0;
            left: 280px;
            right: 0;
            height: 70px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            z-index: 99;
        }
        .header-left { display: flex; align-items: center; gap: 1rem; }
        .header-logo { display: flex; align-items: center; gap: 0.5rem; font-size: 1.25rem; font-weight: 800; color: #10b981; text-decoration: none; }
        .header-logo i { font-size: 1.5rem; }
        .header-right { display: flex; align-items: center; gap: 1.5rem; }
        .notification-btn {
            position: relative;
            width: 44px;
            height: 44px;
            background: #f1f5f9;
            border: none;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .notification-btn:hover { background: #e2e8f0; transform: scale(1.05); }
        .notification-btn i { font-size: 1.125rem; color: #64748b; }
        .notification-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 18px;
            height: 18px;
            background: #ef4444;
            color: white;
            font-size: 0.625rem;
            font-weight: 700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: #f8fafc;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .admin-profile:hover { background: #f1f5f9; }
        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }
        .admin-info { display: flex; flex-direction: column; }
        .admin-name { font-weight: 600; font-size: 0.875rem; color: #1e293b; }
        .admin-role { font-size: 0.75rem; color: #64748b; }

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
        .nav-badge {
            margin-left: auto;
            background: #ef4444;
            color: white;
            font-size: 0.625rem;
            font-weight: 700;
            padding: 0.25rem 0.625rem;
            border-radius: 20px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            margin-top: 70px;
            padding: 2rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        .stat-card.primary::before { background: linear-gradient(90deg, #10b981, #0ea5e9); }
        .stat-card.warning::before { background: linear-gradient(90deg, #f59e0b, #ef4444); }
        .stat-card.info::before { background: linear-gradient(90deg, #0ea5e9, #8b5cf6); }
        .stat-card.success::before { background: linear-gradient(90deg, #22c55e, #10b981); }
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .stat-card.primary .stat-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .stat-card.warning .stat-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .stat-card.info .stat-icon { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
        .stat-card.success .stat-icon { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .stat-value { font-size: 2.5rem; font-weight: 800; color: #1e293b; line-height: 1; }
        .stat-label { font-size: 0.875rem; font-weight: 600; color: #64748b; margin-top: 0.5rem; }

        /* Filters */
        .filters-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }
        .search-box input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.9375rem;
            transition: all 0.3s;
        }
        .search-box input:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .filter-select {
            padding: 0.875rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.9375rem;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        .filter-select:focus { outline: none; border-color: #10b981; }
        .filter-btn {
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .filter-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4); }

        /* Table */
        .table-section {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .table-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .table-title i { color: #10b981; }
        .table-wrapper {
            overflow-x: auto;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
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
            letter-spacing: 0.05em;
            white-space: nowrap;
        }
        .data-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.3s;
        }
        .data-table tbody tr:hover { background: #f8fafc; }
        .data-table td {
            padding: 1rem 1rem;
            white-space: nowrap;
        }
        .assoc-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 200px;
        }
        .assoc-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .assoc-name { 
            font-weight: 600; 
            color: #1e293b; 
            font-size: 0.9375rem;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .assoc-email { 
            color: #64748b; 
            font-size: 0.8125rem; 
            margin-top: 0.125rem;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
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
        .status-badge.validated { background: #dcfce7; color: #16a34a; }
        .status-badge.pending { background: #fef3c7; color: #d97706; }
        .needs-count {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            background: #f1f5f9;
            border-radius: 20px;
            font-weight: 600;
            color: #475569;
        }
        .action-btns {
            display: flex;
            gap: 0.5rem;
        }
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
            font-size: 0.875rem;
        }
        .action-btn.view { background: #dbeafe; color: #0284c7; }
        .action-btn.view:hover { background: #0284c7; color: white; transform: scale(1.1); }
        .action-btn.approve { background: #dcfce7; color: #16a34a; }
        .action-btn.approve:hover { background: #16a34a; color: white; transform: scale(1.1); }
        .action-btn.reject { background: #fee2e2; color: #dc2626; }
        .action-btn.reject:hover { background: #dc2626; color: white; transform: scale(1.1); }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        .modal-header {
            padding: 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }
        .modal-close {
            width: 40px;
            height: 40px;
            background: #f1f5f9;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .modal-close:hover { background: #e2e8f0; transform: rotate(90deg); }
        .modal-body {
            padding: 2rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .info-item {
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 12px;
        }
        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
        }
        .needs-list {
            margin-top: 2rem;
        }
        .needs-list-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .need-card {
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
        }
        .need-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.75rem;
        }
        .need-title { font-weight: 600; color: #1e293b; font-size: 1rem; }
        .need-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.8125rem;
            color: #64748b;
        }
        .need-meta-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        .modal-actions {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        .modal-btn {
            padding: 0.875rem 1.75rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .modal-btn.approve {
            background: linear-gradient(135deg, #22c55e, #10b981);
            color: white;
        }
        .modal-btn.approve:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(34, 197, 94, 0.4); }
        .modal-btn.reject {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        .modal-btn.reject:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4); }
        .comment-section {
            margin-top: 2rem;
        }
        .comment-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        .comment-textarea {
            width: 100%;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9375rem;
            resize: vertical;
            min-height: 100px;
        }
        .comment-textarea:focus { outline: none; border-color: #10b981; }

        /* Footer */
        .footer {
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 1.5rem 2rem;
            margin-top: 3rem;
            text-align: center;
        }
        .footer-text {
            color: #64748b;
            font-size: 0.875rem;
        }
        .footer-links {
            display: flex;
            gap: 2rem;
            justify-content: center;
            margin-top: 0.75rem;
        }
        .footer-link {
            color: #10b981;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .footer-link:hover { text-decoration: underline; }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-brand span, .nav-item span, .nav-badge { display: none; }
            .top-header { left: 80px; }
            .main-content { margin-left: 80px; }
        }
        @media (max-width: 768px) {
            .top-header { left: 0; padding: 0 1rem; }
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; padding: 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .filters-section { flex-direction: column; }
            .search-box { width: 100%; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="top-header">
        <div class="header-left">
            <a href="index.php" class="header-logo">
                <i class="fas fa-hand-holding-heart"></i>
                <span>SolidaritéConnect</span>
            </a>
        </div>
        <div class="header-right">
            <button class="notification-btn">
                <i class="fas fa-bell"></i>
                <?php if($count_pending > 0): ?>
                    <span class="notification-badge"><?= $count_pending ?></span>
                <?php endif; ?>
            </button>
            <div class="admin-profile">
                <div class="admin-avatar">A</div>
                <div class="admin-info">
                    <div class="admin-name">Administrateur</div>
                    <div class="admin-role">Admin</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fas fa-heart"></i>
            </div>
            <div class="brand-text">Solidarité<span>Connect</span></div>
        </div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="admin_associations.php" class="nav-item">
                <i class="fas fa-building"></i>
                <span>Associations</span>
                <?php if($count_pending > 0): ?>
                    <span class="nav-badge"><?= $count_pending ?></span>
                <?php endif; ?>
            </a>
            <a href="admin_needs.php" class="nav-item">
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

    <!-- Main Content -->
    <main class="main-content">
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <p class="stat-value"><?= $count_assoc ?></p>
                <p class="stat-label">Total Associations</p>
            </div>
            <div class="stat-card warning">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <p class="stat-value"><?= $count_pending ?></p>
                <p class="stat-label">En Attente</p>
            </div>
            <div class="stat-card info">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <p class="stat-value"><?= $count_citizens ?></p>
                <p class="stat-label">Donateurs</p>
            </div>
            <div class="stat-card success">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                </div>
                <p class="stat-value"><?= $count_needs ?></p>
                <p class="stat-label">Besoins Publiés</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher par nom, email ou ville..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <select class="filter-select" id="statusFilter">
                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Tous les statuts</option>
                <option value="validated" <?= $status_filter === 'validated' ? 'selected' : '' ?>>Validées</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>En attente</option>
            </select>
            <button class="filter-btn" onclick="applyFilters()">
                <i class="fas fa-filter"></i> Filtrer
            </button>
        </div>

        <!-- Table -->
        <section class="table-section">
            <div class="table-header">
                <h2 class="table-title">
                    <i class="fas fa-building"></i>
                    Gestion des Associations
                </h2>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Association</th>
                            <th>Email</th>
                            <th>Ville</th>
                            <th>Statut</th>
                            <th>Besoins</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($associations as $assoc): ?>
                        <tr>
                            <td><strong>#<?= $assoc['id'] ?></strong></td>
                            <td>
                                <div class="assoc-cell">
                                    <div class="assoc-avatar">
                                        <?= strtoupper(substr($assoc['association_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="assoc-name" title="<?= htmlspecialchars($assoc['association_name']) ?>">
                                            <?= htmlspecialchars($assoc['association_name']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="assoc-email" title="<?= htmlspecialchars($assoc['email']) ?>">
                                    <?= htmlspecialchars($assoc['email']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars(substr($assoc['address'] ?? 'N/A', 0, 20)) ?><?= strlen($assoc['address'] ?? '') > 20 ? '...' : '' ?></td>
                            <td>
                                <?php if($assoc['is_validated']): ?>
                                    <span class="status-badge validated">
                                        <i class="fas fa-check-circle"></i> Validée
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge pending">
                                        <i class="fas fa-clock"></i> En attente
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="needs-count">
                                    <i class="fas fa-box"></i> <?= $assoc['needs_count'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button class="action-btn view" onclick="viewAssociation(<?= $assoc['id'] ?>)" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if(!$assoc['is_validated']): ?>
                                        <button class="action-btn approve" onclick="approveAssociation(<?= $assoc['id'] ?>)" title="Approuver">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="action-btn reject" onclick="rejectAssociation(<?= $assoc['id'] ?>)" title="Rejeter">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <p class="footer-text">© 2024 Solidarité Connect. Tous droits réservés.</p>
            <div class="footer-links">
                <a href="#" class="footer-link">Contact</a>
                <a href="#" class="footer-link">Confidentialité</a>
                <a href="#" class="footer-link">Conditions d'utilisation</a>
            </div>
        </footer>
    </main>

    <!-- Modal -->
    <div class="modal" id="associationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Détails de l'Association</h3>
                <button class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>

    <script>
        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            window.location.href = `admin_dashboard.php?search=${encodeURIComponent(search)}&status=${status}`;
        }

        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });

        function viewAssociation(id) {
            fetch(`api/get_associations.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const assoc = data.association;
                        const needs = data.needs;
                        
                        let needsHtml = '';
                        if (needs.length > 0) {
                            needsHtml = needs.map(need => `
                                <div class="need-card">
                                    <div class="need-header">
                                        <div class="need-title">${need.title}</div>
                                        <span class="status-badge ${need.status}">${need.status}</span>
                                    </div>
                                    <div class="need-meta">
                                        <span class="need-meta-item">
                                            <i class="fas fa-tag"></i> ${need.type_name}
                                        </span>
                                        <span class="need-meta-item">
                                            <i class="fas fa-box"></i> ${need.quantity || 'N/A'}
                                        </span>
                                        <span class="need-meta-item">
                                            <i class="fas fa-map-marker-alt"></i> ${need.location || 'N/A'}
                                        </span>
                                        <span class="need-meta-item">
                                            <i class="fas fa-calendar"></i> ${need.created_at}
                                        </span>
                                    </div>
                                </div>
                            `).join('');
                        } else {
                            needsHtml = '<p style="color: #64748b; text-align: center; padding: 2rem;">Aucun besoin publié</p>';
                        }

                        document.getElementById('modalBody').innerHTML = `
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">Nom de l'association</div>
                                    <div class="info-value">${assoc.association_name}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Email</div>
                                    <div class="info-value">${assoc.email}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Téléphone</div>
                                    <div class="info-value">${assoc.phone || 'N/A'}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Adresse</div>
                                    <div class="info-value">${assoc.address || 'N/A'}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Statut</div>
                                    <div class="info-value">
                                        ${assoc.is_validated ? '<span class="status-badge validated"><i class="fas fa-check-circle"></i> Validée</span>' : '<span class="status-badge pending"><i class="fas fa-clock"></i> En attente</span>'}
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Nombre de besoins</div>
                                    <div class="info-value">${needs.length}</div>
                                </div>
                            </div>
                            
                            <div class="needs-list">
                                <h4 class="needs-list-title">
                                    <i class="fas fa-clipboard-list"></i>
                                    Liste des besoins
                                </h4>
                                ${needsHtml}
                            </div>

                            ${!assoc.is_validated ? `
                                <div class="comment-section">
                                    <label class="comment-label">Commentaire Admin (optionnel)</label>
                                    <textarea class="comment-textarea" id="adminComment" placeholder="Ajouter un commentaire..."></textarea>
                                </div>
                                <div class="modal-actions">
                                    <button class="modal-btn approve" onclick="approveAssociation(${assoc.id})">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                    <button class="modal-btn reject" onclick="rejectAssociation(${assoc.id})">
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                </div>
                            ` : ''}
                        `;
                        
                        document.getElementById('associationModal').classList.add('active');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function closeModal() {
            document.getElementById('associationModal').classList.remove('active');
        }

        function approveAssociation(id) {
            if (confirm('Êtes-vous sûr de vouloir approuver cette association ?')) {
                window.location.href = `valider_assoc.php?id=${id}`;
            }
        }

        function rejectAssociation(id) {
            if (confirm('Êtes-vous sûr de vouloir rejeter cette association ? Elle sera supprimée définitivement.')) {
                window.location.href = `rejeter_assoc.php?id=${id}`;
            }
        }

        // Close modal on outside click
        document.getElementById('associationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>

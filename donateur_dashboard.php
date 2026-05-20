<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: login.php");
    exit();
}

// Récupérer l'utilisateur
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$_SESSION['user_id']]);
$user = $stmt_user->fetch();

// Récupérer les filtres
$search = $_GET['search'] ?? '';
$filter_type = $_GET['type'] ?? '';
$filter_urgency = $_GET['urgency'] ?? '';

// Construire la requête pour les associations validées avec leurs besoins
$sql = "SELECT a.*, 
        (SELECT COUNT(*) FROM needs WHERE association_id = a.id AND status != 'satisfied') as active_needs,
        r.name as region_name
        FROM associations a 
        LEFT JOIN regions r ON a.region_id = r.id
        WHERE a.is_validated = 1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (a.association_name LIKE :search OR a.address LIKE :search)";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY a.association_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$associations = $stmt->fetchAll();
$total_associations = count($associations);

// Statistiques
$total_assoc = count($associations);
$total_needs = $pdo->query("SELECT COUNT(*) FROM needs n JOIN associations a ON n.association_id = a.id WHERE a.is_validated = 1 AND n.status != 'satisfied'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Donateur - Solidarité Connect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: #f8fafc; 
            min-height: 100vh;
        }

        /* Header */
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            z-index: 100;
        }
        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 800;
            color: #10b981;
            text-decoration: none;
        }
        .header-logo span {
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .header-logo i { font-size: 1.5rem; }
        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
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
        .notification-btn:hover { 
            background: #e2e8f0;
            transform: scale(1.05);
        }
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
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: #f8fafc;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .user-profile:hover { background: #f1f5f9; }
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
        }
        .user-info { display: flex; flex-direction: column; }
        .user-name { font-weight: 600; font-size: 0.875rem; color: #1e293b; }
        .user-role { font-size: 0.75rem; color: #64748b; }

        /* Main Content */
        .main-content {
            margin-top: 70px;
            padding: 2rem;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #10b981, #059669, #0ea5e9);
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 2rem;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2);
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(ellipse at 20% 30%, rgba(255,255,255,0.15) 0%, transparent 50%);
        }
        .hero-content { 
            position: relative; 
            z-index: 1;
        }
        .hero-title { 
            font-size: 2rem; 
            font-weight: 800; 
            margin-bottom: 0.5rem;
        }
        .hero-subtitle { 
            font-size: 1.125rem; 
            opacity: 0.95;
        }

        /* Stats */
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
            transition: all 0.3s ease;
        }
        .stat-card:hover { 
            transform: translateY(-4px); 
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15);
        }
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
            transition: all 0.3s ease;
        }
        .stat-card.primary .stat-icon { 
            background: rgba(16, 185, 129, 0.1); 
            color: #10b981;
        }
        .stat-card.secondary .stat-icon { 
            background: rgba(14, 165, 233, 0.1); 
            color: #0ea5e9;
        }
        .stat-card:hover .stat-icon {
            transform: scale(1.1);
        }
        .stat-value { 
            font-size: 2.5rem; 
            font-weight: 800; 
            color: #1e293b;
            line-height: 1;
        }
        .stat-label { 
            font-size: 0.875rem; 
            font-weight: 600; 
            color: #64748b; 
            margin-top: 0.5rem;
        }

        /* Search & Filters */
        .search-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }
        .search-box input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
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
            font-size: 1.125rem;
        }
        .filter-btn {
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .filter-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4); }

        /* Associations Grid */
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .section-title i { 
            color: #10b981;
        }
        .associations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        .assoc-card {
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .assoc-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.15);
            border-color: #10b981;
        }
        .assoc-header {
            display: flex;
            align-items: start;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .assoc-avatar {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
            transition: all 0.3s ease;
        }
        .assoc-card:hover .assoc-avatar {
            transform: scale(1.05);
        }
        .assoc-info { flex: 1; }
        .assoc-name {
            font-weight: 700;
            font-size: 1.125rem;
            color: #1e293b;
            margin-bottom: 0.375rem;
        }
        .assoc-location {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.875rem;
            color: #64748b;
        }
        .assoc-details {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }
        .detail-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
            color: #475569;
        }
        .detail-icon {
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #10b981;
        }
        .needs-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            background: linear-gradient(135deg, #d1fae5, #bae6fd);
            border-radius: 10px;
            font-weight: 600;
            color: #047857;
            font-size: 0.875rem;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.15);
        }
        .view-needs-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .view-needs-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            animation: fade-in 0.3s ease-out;
        }
        .modal.active { display: flex; }
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
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
        .modal-body { padding: 2rem; }
        .assoc-modal-info {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .info-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }
        .info-row:last-child { margin-bottom: 0; }
        .info-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #10b981;
        }
        .needs-section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .need-item {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }
        .need-item:hover { border-color: #10b981; background: white; }
        .need-item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        .need-item-title {
            font-weight: 600;
            font-size: 1rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        .need-item-desc {
            font-size: 0.875rem;
            color: #64748b;
            line-height: 1.6;
        }
        .need-status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .need-status.urgent { background: #fee2e2; color: #dc2626; }
        .need-status.normal { background: #dbeafe; color: #0284c7; }
        .need-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .need-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            color: #475569;
        }
        .need-meta-item i { color: #10b981; }
        .need-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .need-action-btn {
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }
        .need-action-btn.favorite {
            background: #fef3c7;
            color: #d97706;
        }
        .need-action-btn.favorite:hover {
            background: #fbbf24;
            color: white;
            transform: scale(1.05);
        }
        .need-action-btn.contact {
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            color: white;
        }
        .need-action-btn.contact:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        /* Footer */
        .footer {
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 2rem;
            margin-top: 3rem;
            text-align: center;
        }
        .footer-text { color: #64748b; font-size: 0.875rem; margin-bottom: 0.75rem; }
        .footer-links {
            display: flex;
            gap: 2rem;
            justify-content: center;
        }
        .footer-link {
            color: #10b981;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .footer-link:hover { text-decoration: underline; }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            grid-column: 1 / -1;
            background: white;
            border-radius: 16px;
            border: 2px dashed #e2e8f0;
        }
        .empty-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.75rem;
        }
        .empty-text { 
            color: #64748b;
            font-size: 1rem;
        }

        /* Scroll to Top Button */
        .scroll-top-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            opacity: 0;
            visibility: hidden;
            transform: translateY(100px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 999;
        }
        .scroll-top-btn.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .scroll-top-btn:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 12px 35px rgba(16, 185, 129, 0.5);
        }
        .scroll-top-btn:active {
            transform: translateY(-2px) scale(1.05);
        }

        /* Tooltip */
        .tooltip {
            position: relative;
            display: inline-block;
        }
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 140px;
            background: #1e293b;
            color: white;
            text-align: center;
            border-radius: 8px;
            padding: 0.5rem;
            position: absolute;
            z-index: 1000;
            bottom: 125%;
            left: 50%;
            margin-left: -70px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #1e293b transparent transparent transparent;
        }
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        /* Loading Skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: loading 1.5s ease-in-out infinite;
            border-radius: 12px;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .skeleton-card {
            background: white;
            border-radius: 20px;
            padding: 1.75rem;
            border: 1px solid #e2e8f0;
        }
        .skeleton-header {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .skeleton-avatar {
            width: 56px;
            height: 56px;
            border-radius: 14px;
        }
        .skeleton-info {
            flex: 1;
        }
        .skeleton-title {
            height: 20px;
            width: 60%;
            margin-bottom: 0.5rem;
        }
        .skeleton-subtitle {
            height: 16px;
            width: 40%;
        }
        .skeleton-text {
            height: 14px;
            margin-bottom: 0.75rem;
        }
        .skeleton-button {
            height: 48px;
            width: 100%;
            margin-top: 1rem;
        }

        /* No Results Message */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            grid-column: 1 / -1;
            animation: fade-in 0.5s ease-out;
        }
        .no-results-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        .no-results-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.5rem;
        }
        .no-results-text {
            color: #64748b;
            font-size: 1rem;
        }
        .clear-filters-btn {
            margin-top: 1.5rem;
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .clear-filters-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        @media (max-width: 768px) {
            .top-header { padding: 0 1rem; }
            .main-content { padding: 1rem; }
            .hero-section { padding: 2rem 1.5rem; }
            .hero-title { font-size: 1.5rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .associations-grid { grid-template-columns: 1fr; }
            .scroll-top-btn {
                bottom: 1rem;
                right: 1rem;
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="top-header">
        <a href="index.php" class="header-logo">
            <i class="fas fa-hand-holding-heart"></i>
            <span>SolidaritéConnect</span>
        </a>
        <div class="header-right">
            <button class="notification-btn" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-badge"><?= $total_needs ?></span>
            </button>
            <div class="user-profile">
                <div class="user-avatar">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="uploads/profiles/<?= htmlspecialchars($user['profile_photo']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                    <div class="user-role">Donateur</div>
                </div>
            </div>
            <a href="profil_citizen.php" class="notification-btn" title="Mon Profil" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                <i class="fas fa-user-cog" style="color: #10b981;"></i>
            </a>
            <a href="logout.php" class="notification-btn" title="Déconnexion" style="background: #fee2e2;">
                <i class="fas fa-sign-out-alt" style="color: #dc2626;"></i>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">👋 Bienvenue, <?= htmlspecialchars($user['name']) ?> !</h1>
                <p class="hero-subtitle">Découvrez les associations validées et leurs besoins pour faire la différence</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <p class="stat-value"><?= $total_assoc ?></p>
                <p class="stat-label">Associations Validées</p>
            </div>
            <div class="stat-card secondary">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                </div>
                <p class="stat-value"><?= $total_needs ?></p>
                <p class="stat-label">Besoins Actifs</p>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="search-section">
            <form method="GET" id="filterForm">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Rechercher une association..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                </div>
                <?php if(!empty($search)): ?>
                <div style="text-align: center; margin-top: 1rem;">
                    <button type="button" class="filter-btn" onclick="clearAllFilters()" style="background: #ef4444;">
                        <i class="fas fa-times"></i> Réinitialiser
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Associations Grid -->
        <h2 class="section-title">
            <i class="fas fa-building"></i>
            Associations Validées
        </h2>

        <?php if(empty($associations)): ?>
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h3 class="empty-title">Aucune association trouvée</h3>
                <p class="empty-text">
                    <?php if(!empty($search)): ?>
                        Aucune association ne correspond à votre recherche "<?= htmlspecialchars($search) ?>".
                    <?php else: ?>
                        Il n'y a pas encore d'associations validées disponibles.
                    <?php endif; ?>
                </p>
                <?php if(!empty($search)): ?>
                    <button class="clear-filters-btn" onclick="window.location.href='donateur_dashboard.php'">
                        <i class="fas fa-times"></i> Réinitialiser la recherche
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="associations-grid">
                <?php foreach($associations as $assoc): ?>
                    <div class="assoc-card" onclick="viewAssociationNeeds(<?= $assoc['id'] ?>)">
                        <div class="assoc-header">
                            <div class="assoc-avatar">
                                <?= strtoupper(substr($assoc['association_name'], 0, 1)) ?>
                            </div>
                            <div class="assoc-info">
                                <div class="assoc-name"><?= htmlspecialchars($assoc['association_name']) ?></div>
                                <div class="assoc-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($assoc['address'] ?? $assoc['region_name'] ?? 'Non spécifié') ?>
                                </div>
                            </div>
                        </div>

                        <div class="assoc-details">
                            <?php if(!empty($assoc['phone'])): ?>
                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <span><?= htmlspecialchars($assoc['phone']) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="detail-row">
                                <div class="detail-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <span class="needs-badge">
                                    <i class="fas fa-box"></i>
                                    <?= $assoc['active_needs'] ?> besoin<?= $assoc['active_needs'] > 1 ? 's' : '' ?> actif<?= $assoc['active_needs'] > 1 ? 's' : '' ?>
                                </span>
                            </div>
                        </div>

                        <button class="view-needs-btn" onclick="event.stopPropagation(); viewAssociationNeeds(<?= $assoc['id'] ?>)">
                            <i class="fas fa-eye"></i>
                            Voir les besoins
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <footer class="footer">
            <p class="footer-text">© 2024 Solidarité Connect. Tous droits réservés.</p>
            <div class="footer-links">
                <a href="#" class="footer-link">Contact</a>
                <a href="#" class="footer-link">Politique de confidentialité</a>
            </div>
        </footer>
    </main>

    <!-- Scroll to Top Button -->
    <button class="scroll-top-btn" id="scrollTopBtn" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Modal -->
    <div class="modal" id="needsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Besoins de l'Association</h3>
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
        // Scroll to Top Button
        const scrollTopBtn = document.getElementById('scrollTopBtn');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollTopBtn.classList.add('visible');
            } else {
                scrollTopBtn.classList.remove('visible');
            }
        });
        
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Animated Counter for Stats
        function animateCounter(element, target, duration = 1000) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(start);
                }
            }, 16);
        }

        // Animate stats on page load
        window.addEventListener('load', () => {
            const statValues = document.querySelectorAll('.stat-value');
            statValues.forEach(stat => {
                const target = parseInt(stat.textContent);
                stat.textContent = '0';
                setTimeout(() => animateCounter(stat, target), 300);
            });
        });

        // Real-time Search Filter
        const searchInput = document.querySelector('input[name="search"]');
        const assocCards = document.querySelectorAll('.assoc-card');
        
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                let visibleCount = 0;
                
                assocCards.forEach(card => {
                    const name = card.querySelector('.assoc-name').textContent.toLowerCase();
                    const location = card.querySelector('.assoc-location').textContent.toLowerCase();
                    
                    if (name.includes(searchTerm) || location.includes(searchTerm)) {
                        card.style.display = 'block';
                        card.style.animation = 'fade-in-up 0.4s ease-out';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Show/hide no results message
                const existingNoResults = document.querySelector('.no-results');
                if (existingNoResults) {
                    existingNoResults.remove();
                }
                
                if (visibleCount === 0 && searchTerm !== '') {
                    const grid = document.querySelector('.associations-grid');
                    const noResults = document.createElement('div');
                    noResults.className = 'no-results';
                    noResults.innerHTML = `
                        <div class="no-results-icon">🔍</div>
                        <h3 class="no-results-title">Aucun résultat trouvé</h3>
                        <p class="no-results-text">Essayez avec d'autres mots-clés</p>
                        <button class="clear-filters-btn" onclick="clearSearch()">
                            <i class="fas fa-times"></i> Effacer la recherche
                        </button>
                    `;
                    grid.appendChild(noResults);
                }
            });
        }

        function clearSearch() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }

        // Smooth scroll for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        function viewAssociationNeeds(assocId) {
            console.log('=== Function called with ID:', assocId);
            
            // Test: Show modal immediately
            const modal = document.getElementById('needsModal');
            console.log('Modal element:', modal);
            console.log('Modal classes before:', modal.className);
            
            fetch(`api/get_needs.php?association_id=${assocId}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response OK:', response.ok);
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    
                    if (data.success) {
                        const assoc = data.association;
                        const needs = data.needs;
                        
                        console.log('Association:', assoc);
                        console.log('Needs count:', needs.length);
                        
                        document.getElementById('modalTitle').textContent = `Besoins de ${assoc.association_name}`;
                        
                        let needsHtml = `
                            <div class="assoc-modal-info">
                                <div class="info-row">
                                    <div class="info-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div>
                                        <strong>${assoc.association_name}</strong>
                                    </div>
                                </div>
                                ${assoc.address ? `
                                    <div class="info-row">
                                        <div class="info-icon">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div>${assoc.address}</div>
                                    </div>
                                ` : ''}
                                ${assoc.phone ? `
                                    <div class="info-row">
                                        <div class="info-icon">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <div>${assoc.phone}</div>
                                    </div>
                                ` : ''}
                            </div>
                            
                            <h4 class="needs-section-title">
                                <i class="fas fa-clipboard-list"></i>
                                Liste des besoins (${needs.length})
                            </h4>
                        `;
                        
                        if (needs.length > 0) {
                            needs.forEach(need => {
                                needsHtml += `
                                    <div class="need-item">
                                        <div class="need-item-header">
                                            <div>
                                                <div class="need-item-title">${need.title}</div>
                                                <div class="need-item-desc">${need.description}</div>
                                            </div>
                                            <span class="need-status ${need.status}">
                                                ${need.status === 'urgent' ? '<i class="fas fa-fire"></i> Urgent' : '<i class="fas fa-clock"></i> Normal'}
                                            </span>
                                        </div>
                                        
                                        <div class="need-meta-grid">
                                            ${need.quantity ? `
                                                <div class="need-meta-item">
                                                    <i class="fas fa-box"></i>
                                                    <span>Quantité: ${need.quantity}</span>
                                                </div>
                                            ` : ''}
                                            ${need.location ? `
                                                <div class="need-meta-item">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <span>${need.location}</span>
                                                </div>
                                            ` : ''}
                                            ${need.deadline ? `
                                                <div class="need-meta-item">
                                                    <i class="fas fa-calendar"></i>
                                                    <span>Avant le ${new Date(need.deadline).toLocaleDateString('fr-FR')}</span>
                                                </div>
                                            ` : ''}
                                            <div class="need-meta-item">
                                                <i class="fas fa-tag"></i>
                                                <span>${need.type_name}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="need-actions">
                                            <button class="need-action-btn favorite" onclick="toggleFavorite(${need.id})">
                                                <i class="fas fa-star"></i>
                                                Marquer comme favori
                                            </button>
                                            ${assoc.phone ? `
                                                <a href="tel:${assoc.phone}" class="need-action-btn contact" style="text-decoration: none;">
                                                    <i class="fas fa-phone"></i>
                                                    Appeler
                                                </a>
                                            ` : ''}
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            needsHtml += `
                                <div style="text-align: center; padding: 3rem; color: #64748b;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                    <p>Aucun besoin actif pour le moment</p>
                                </div>
                            `;
                        }
                        
                        document.getElementById('modalBody').innerHTML = needsHtml;
                        
                        console.log('Adding active class to modal...');
                        modal.classList.add('active');
                        console.log('Modal classes after:', modal.className);
                        
                    } else {
                        console.error('API returned success: false', data.message);
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Erreur lors du chargement des besoins: ' + error.message);
                });
        }

        function closeModal() {
            document.getElementById('needsModal').classList.remove('active');
        }

        function toggleFavorite(needId) {
            // Placeholder for favorite functionality
            alert('Fonctionnalité "Favori" - Besoin #' + needId + ' marqué comme favori!');
        }

        // Close modal on outside click
        document.getElementById('needsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Clear all filters
        function clearAllFilters() {
            window.location.href = window.location.pathname;
        }

        // Add ripple effect to buttons (excluding view-needs-btn to avoid conflicts)
        document.querySelectorAll('.filter-btn, .need-action-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.6)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple 0.6s ease-out';
                ripple.style.pointerEvents = 'none';
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Show notification count animation
        const notificationBadge = document.querySelector('.notification-badge');
        if (notificationBadge) {
            setInterval(() => {
                notificationBadge.style.animation = 'none';
                setTimeout(() => {
                    notificationBadge.style.animation = 'pulse-badge 2s ease-in-out infinite';
                }, 10);
            }, 5000);
        }
    </script>
</body>
</html>

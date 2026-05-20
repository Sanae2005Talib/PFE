<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Statistiques générales
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_associations = $pdo->query("SELECT COUNT(*) FROM associations")->fetchColumn();
$total_needs = $pdo->query("SELECT COUNT(*) FROM needs")->fetchColumn();
$total_citizens = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'citizen'")->fetchColumn();

// Associations par statut
$validated_assoc = $pdo->query("SELECT COUNT(*) FROM associations WHERE is_validated = 1")->fetchColumn();
$pending_assoc = $pdo->query("SELECT COUNT(*) FROM associations WHERE is_validated = 0")->fetchColumn();

// Besoins par statut
$urgent_needs = $pdo->query("SELECT COUNT(*) FROM needs WHERE status = 'urgent'")->fetchColumn();
$normal_needs = $pdo->query("SELECT COUNT(*) FROM needs WHERE status = 'normal'")->fetchColumn();
$satisfied_needs = $pdo->query("SELECT COUNT(*) FROM needs WHERE status = 'satisfied'")->fetchColumn();

// Besoins par type
$needs_by_type = $pdo->query("
    SELECT dt.name, COUNT(n.id) as count
    FROM donation_types dt
    LEFT JOIN needs n ON dt.id = n.donation_type_id
    GROUP BY dt.id, dt.name
    ORDER BY count DESC
")->fetchAll();

// Associations par région
$assoc_by_region = $pdo->query("
    SELECT r.name, COUNT(a.id) as count
    FROM regions r
    LEFT JOIN associations a ON r.id = a.region_id
    GROUP BY r.id, r.name
    ORDER BY count DESC
")->fetchAll();

// Top 5 associations avec le plus de besoins
$top_associations = $pdo->query("
    SELECT a.association_name, COUNT(n.id) as needs_count
    FROM associations a
    LEFT JOIN needs n ON a.id = n.association_id
    WHERE a.is_validated = 1
    GROUP BY a.id, a.association_name
    ORDER BY needs_count DESC
    LIMIT 5
")->fetchAll();

// Statistiques supplémentaires
$avg_needs_per_assoc = $pdo->query("
    SELECT ROUND(AVG(needs_count), 1) as avg_needs
    FROM (
        SELECT COUNT(n.id) as needs_count
        FROM associations a
        LEFT JOIN needs n ON a.id = n.association_id
        WHERE a.is_validated = 1
        GROUP BY a.id
    ) as subquery
")->fetchColumn();

$validation_rate = $total_associations > 0 ? round(($validated_assoc / $total_associations) * 100, 1) : 0;
$satisfaction_rate = $total_needs > 0 ? round(($satisfied_needs / $total_needs) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports - Admin</title>
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
        }
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
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .stat-card.primary .stat-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .stat-card.warning .stat-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .stat-card.info .stat-icon { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
        .stat-card.success .stat-icon { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .stat-value { font-size: 2.5rem; font-weight: 800; color: #1e293b; }
        .stat-label { font-size: 0.875rem; font-weight: 600; color: #64748b; margin-top: 0.5rem; }

        /* Report Cards */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .report-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid #e2e8f0;
        }
        .report-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .report-title i { color: #10b981; }
        .report-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 0.75rem;
        }
        .report-item:last-child { margin-bottom: 0; }
        .report-label { font-weight: 600; color: #475569; }
        .report-value {
            font-size: 1.25rem;
            font-weight: 800;
            color: #10b981;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #0ea5e9);
            border-radius: 10px;
            transition: width 0.3s;
        }

        /* Chart Container */
        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid #e2e8f0;
        }
        .chart-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .chart-title i { color: #10b981; }

        /* Pie Chart */
        .pie-chart {
            width: 200px;
            height: 200px;
            margin: 0 auto;
            position: relative;
        }
        .pie-legend {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }
        .legend-label {
            flex: 1;
            font-size: 0.875rem;
            color: #475569;
        }
        .legend-value {
            font-weight: 700;
            color: #1e293b;
        }

        /* Bar Chart */
        .bar-chart {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .bar-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .bar-label {
            min-width: 120px;
            font-size: 0.875rem;
            font-weight: 600;
            color: #475569;
        }
        .bar-track {
            flex: 1;
            height: 32px;
            background: #f1f5f9;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #0ea5e9);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 0.75rem;
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
            transition: width 0.5s ease;
        }

        /* Metric Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .metric-card {
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            border-radius: 16px;
            padding: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        .metric-icon {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .metric-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        .metric-label {
            font-size: 0.875rem;
            opacity: 0.9;
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
            <a href="admin_users.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="admin_reports.php" class="nav-item active">
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
            <h1 class="page-title">Rapports & Statistiques</h1>
            <p class="page-subtitle">Vue d'ensemble des données de la plateforme</p>
        </div>

        <!-- Metrics -->
        <div class="metrics-grid">
            <div class="metric-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="metric-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="metric-value"><?= $validation_rate ?>%</div>
                <div class="metric-label">Taux de Validation</div>
            </div>
            <div class="metric-card" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                <div class="metric-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="metric-value"><?= $avg_needs_per_assoc ?? 0 ?></div>
                <div class="metric-label">Besoins / Association</div>
            </div>
            <div class="metric-card" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                <div class="metric-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="metric-value"><?= $satisfaction_rate ?>%</div>
                <div class="metric-label">Taux de Satisfaction</div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <p class="stat-value"><?= $total_users ?></p>
                <p class="stat-label">Total Utilisateurs</p>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <p class="stat-value"><?= $total_associations ?></p>
                <p class="stat-label">Total Associations</p>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <p class="stat-value"><?= $total_needs ?></p>
                <p class="stat-label">Total Besoins</p>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <p class="stat-value"><?= $total_citizens ?></p>
                <p class="stat-label">Donateurs Actifs</p>
            </div>
        </div>

        <!-- Reports -->
        <div class="reports-grid">
            <!-- Besoins par statut - Pie Chart Style -->
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-chart-pie"></i>
                    Besoins par Statut
                </h3>
                <div class="pie-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #dc2626;"></div>
                        <div class="legend-label">Urgent</div>
                        <div class="legend-value"><?= $urgent_needs ?></div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #0284c7;"></div>
                        <div class="legend-label">Normal</div>
                        <div class="legend-value"><?= $normal_needs ?></div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #10b981;"></div>
                        <div class="legend-label">Satisfait</div>
                        <div class="legend-value"><?= $satisfied_needs ?></div>
                    </div>
                </div>
            </div>

            <!-- Associations par statut -->
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-building"></i>
                    Associations par Statut
                </h3>
                <div class="bar-chart">
                    <div class="bar-item">
                        <div class="bar-label">Validées</div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?= $total_associations > 0 ? ($validated_assoc / $total_associations * 100) : 0 ?>%">
                                <?= $validated_assoc ?>
                            </div>
                        </div>
                    </div>
                    <div class="bar-item">
                        <div class="bar-label">En Attente</div>
                        <div class="bar-track">
                            <div class="bar-fill" style="background: linear-gradient(90deg, #f59e0b, #ef4444); width: <?= $total_associations > 0 ? ($pending_assoc / $total_associations * 100) : 0 ?>%">
                                <?= $pending_assoc ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Besoins par type -->
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-tags"></i>
                    Besoins par Type
                </h3>
                <div class="bar-chart">
                    <?php 
                    $max_type = max(array_column($needs_by_type, 'count'));
                    foreach($needs_by_type as $type): 
                    ?>
                    <div class="bar-item">
                        <div class="bar-label"><?= htmlspecialchars($type['name']) ?></div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?= $max_type > 0 ? ($type['count'] / $max_type * 100) : 0 ?>%">
                                <?= $type['count'] ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Associations par région -->
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Associations par Région
                </h3>
                <div class="bar-chart">
                    <?php 
                    $max_region = max(array_column($assoc_by_region, 'count'));
                    foreach($assoc_by_region as $region): 
                    ?>
                    <div class="bar-item">
                        <div class="bar-label"><?= htmlspecialchars($region['name']) ?></div>
                        <div class="bar-track">
                            <div class="bar-fill" style="background: linear-gradient(90deg, #8b5cf6, #7c3aed); width: <?= $max_region > 0 ? ($region['count'] / $max_region * 100) : 0 ?>%">
                                <?= $region['count'] ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Associations -->
            <div class="chart-card" style="grid-column: 1 / -1;">
                <h3 class="chart-title">
                    <i class="fas fa-trophy"></i>
                    Top 5 Associations (Plus de Besoins)
                </h3>
                <div class="bar-chart">
                    <?php 
                    $max_needs = $top_associations[0]['needs_count'] ?? 1;
                    foreach($top_associations as $assoc): 
                    ?>
                    <div class="bar-item">
                        <div class="bar-label" style="min-width: 200px;"><?= htmlspecialchars($assoc['association_name']) ?></div>
                        <div class="bar-track">
                            <div class="bar-fill" style="background: linear-gradient(90deg, #f59e0b, #ef4444); width: <?= $max_needs > 0 ? ($assoc['needs_count'] / $max_needs * 100) : 0 ?>%">
                                <?= $assoc['needs_count'] ?> besoins
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Animate bars on load
        window.addEventListener('load', () => {
            const bars = document.querySelectorAll('.bar-fill');
            bars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });
    </script>
</body>
</html>

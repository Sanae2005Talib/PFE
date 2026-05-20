<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'association') {
    header("Location: login.php");
    exit();
}

$stmt_assoc = $pdo->prepare("SELECT * FROM associations WHERE user_id = ?");
$stmt_assoc->execute([$_SESSION['user_id']]);
$assoc = $stmt_assoc->fetch();

$stmt_needs = $pdo->prepare("
    SELECT n.*, t.name as type_name 
    FROM needs n 
    JOIN donation_types t ON n.donation_type_id = t.id 
    WHERE n.association_id = ? 
    ORDER BY n.id DESC
");
$stmt_needs->execute([$assoc['id']]);
$needs = $stmt_needs->fetchAll();

$total_needs = count($needs);
$urgent_needs = count(array_filter($needs, fn($n) => $n['status'] === 'urgent'));
$satisfied_needs = count(array_filter($needs, fn($n) => $n['status'] === 'satisfied'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace - Solidarité Connect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Alert Notifications */
        .alert {
            position: fixed;
            top: 2rem;
            right: 2rem;
            max-width: 400px;
            padding: 1.25rem 1.5rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-2xl);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 9999;
            animation: slideInRight 0.4s ease-out;
            backdrop-filter: blur(10px);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.95), rgba(16, 185, 129, 0.95));
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(220, 38, 38, 0.95));
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .alert-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .alert-message {
            font-size: 0.875rem;
            opacity: 0.95;
        }

        body {
            background: var(--gray-50);
            min-height: 100vh;
        }

        /* Navbar Dashboard */
        .dash-navbar {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .dash-navbar-inner {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--white);
            padding: 0.5rem 1.25rem 0.5rem 0.5rem;
            border-radius: var(--radius-full);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-100);
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1rem;
            font-weight: 800;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 700;
            font-size: 0.9375rem;
            color: var(--gray-800);
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--primary-600);
            font-weight: 600;
        }

        .logout-btn {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--danger);
            text-decoration: none;
            font-size: 1.125rem;
            transition: all var(--transition-base);
        }

        .logout-btn:hover {
            background: var(--danger);
            color: var(--white);
            transform: scale(1.05);
        }

        /* Main */
        .dash-main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: var(--gradient-primary);
            border-radius: var(--radius-3xl);
            padding: 3rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 10% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 90% 80%, rgba(14, 165, 233, 0.2) 0%, transparent 50%);
            pointer-events: none;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .welcome-text h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--white);
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.0625rem;
        }

        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: var(--white);
            color: var(--primary-600);
            border-radius: var(--radius-xl);
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
            transition: all var(--transition-base);
        }

        .add-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.2);
            color: var(--primary-700);
        }

        .add-btn i {
            font-size: 1.25rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--radius-2xl);
            padding: 1.75rem;
            border: 1px solid var(--gray-100);
            position: relative;
            overflow: hidden;
            transition: all var(--transition-base);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-xl);
        }

        .stat-card.total::before { background: var(--gradient-primary); }
        .stat-card.urgent::before { background: linear-gradient(90deg, #EF4444, #F97316); }
        .stat-card.satisfied::before { background: linear-gradient(90deg, #22C55E, #10B981); }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.375rem;
        }

        .stat-card.total .stat-icon {
            background: linear-gradient(135deg, var(--primary-100), var(--secondary-100));
            color: var(--primary-600);
        }

        .stat-card.urgent .stat-icon {
            background: linear-gradient(135deg, #FEE2E2, #FECACA);
            color: var(--danger);
        }

        .stat-card.satisfied .stat-icon {
            background: linear-gradient(135deg, #DCFCE7, #BBF7D0);
            color: var(--success);
        }

        .stat-value {
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--gray-800);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        /* Table Section */
        .table-section {
            background: var(--white);
            border-radius: var(--radius-3xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-100);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.75rem 2rem;
            background: linear-gradient(135deg, var(--gray-50), var(--white));
            border-bottom: 1px solid var(--gray-100);
        }

        .table-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-title i {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Data Table */
        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: var(--gray-50);
        }

        .data-table th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--gray-100);
            transition: background var(--transition-fast);
        }

        .data-table tbody tr:hover {
            background: var(--primary-50);
        }

        .data-table tbody tr:last-child {
            border-bottom: none;
        }

        .data-table td {
            padding: 1.25rem 1.5rem;
        }

        .need-title {
            font-weight: 700;
            font-size: 1rem;
            color: var(--gray-800);
        }

        .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--gray-100), var(--gray-50));
            color: var(--gray-600);
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: var(--radius-full);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-radius: var(--radius-full);
        }

        .status-pill.urgent {
            background: linear-gradient(135deg, #FEE2E2, #FECACA);
            color: var(--danger);
        }

        .status-pill.normal {
            background: linear-gradient(135deg, var(--primary-100), var(--secondary-100));
            color: var(--primary-700);
        }

        .status-pill.satisfied {
            background: linear-gradient(135deg, #DCFCE7, #BBF7D0);
            color: var(--success);
        }

        /* Action Buttons */
        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 0.9375rem;
            transition: all var(--transition-base);
        }

        .action-btn.edit {
            background: linear-gradient(135deg, var(--primary-100), var(--secondary-100));
            color: var(--primary-600);
        }

        .action-btn.edit:hover {
            background: var(--gradient-primary);
            color: var(--white);
            transform: scale(1.1);
        }

        .action-btn.delete {
            background: linear-gradient(135deg, #FEE2E2, #FECACA);
            color: var(--danger);
        }

        .action-btn.delete:hover {
            background: var(--danger);
            color: var(--white);
            transform: scale(1.1);
        }

        /* Empty State */
        .empty-state {
            padding: 5rem 2rem;
            text-align: center;
        }

        .empty-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--gray-100), var(--gray-50));
            border-radius: var(--radius-3xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
            color: var(--gray-300);
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .empty-text {
            color: var(--gray-500);
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .empty-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            padding: 1rem 2rem;
            background: var(--gradient-primary);
            color: var(--white);
            text-decoration: none;
            border-radius: var(--radius-xl);
            font-weight: 700;
            font-size: 1rem;
            transition: all var(--transition-base);
            box-shadow: 0 6px 25px rgba(16, 185, 129, 0.4);
        }

        .empty-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 35px rgba(16, 185, 129, 0.5);
            color: var(--white);
        }

        @media (max-width: 768px) {
            .dash-navbar {
                padding: 1rem;
            }
            .dash-navbar-inner {
                gap: 0.5rem;
            }
            .logo-text {
                font-size: 1.125rem;
            }
            .user-pill {
                padding: 0.375rem 0.5rem;
            }
            .user-info {
                display: none;
            }
            .dash-main {
                padding: 1rem;
            }
            .welcome-banner {
                padding: 1.5rem;
                border-radius: var(--radius-2xl);
                text-align: center;
            }
            .welcome-content {
                flex-direction: column;
                gap: 1.5rem;
            }
            .welcome-text h1 {
                font-size: 1.5rem;
            }
            .add-btn {
                width: 100%;
                justify-content: center;
            }
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .stat-value {
                font-size: 2rem;
            }
            .table-header {
                padding: 1.25rem;
            }
            .table-title {
                font-size: 1rem;
            }
            /* Make table scrollable or more compact */
            .data-table thead {
                display: none;
            }
            .data-table tr {
                display: block;
                padding: 1rem;
                border-bottom: 1px solid var(--gray-100);
            }
            .data-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border: none;
                font-size: 0.875rem;
            }
            .data-table td::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--gray-500);
            }
        }
    </style>
</head>
<body>
    <!-- Notifications -->
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success" id="alert">
            <i class="fas fa-check-circle alert-icon"></i>
            <div class="alert-content">
                <div class="alert-title">Succès !</div>
                <div class="alert-message">
                    <?php 
                    if($_GET['success'] === 'besoin_ajoute') echo 'Votre besoin a été publié avec succès.';
                    elseif($_GET['success'] === 'besoin_modifie') echo 'Votre besoin a été modifié avec succès.';
                    else echo 'Opération réussie.';
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error" id="alert">
            <i class="fas fa-exclamation-circle alert-icon"></i>
            <div class="alert-content">
                <div class="alert-title">Erreur</div>
                <div class="alert-message">
                    <?php 
                    if($_GET['error'] === 'suppression_echouee') echo 'Impossible de supprimer ce besoin.';
                    else echo 'Une erreur est survenue.';
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success" id="alert">
            <i class="fas fa-trash-alt alert-icon"></i>
            <div class="alert-content">
                <div class="alert-title">Supprimé</div>
                <div class="alert-message">Le besoin a été supprimé avec succès.</div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Floating Orbs -->
    <div class="orb orb-1" style="opacity: 0.3;"></div>
    <div class="orb orb-2" style="opacity: 0.3;"></div>

    <!-- Navbar -->
    <nav class="dash-navbar">
        <div class="dash-navbar-inner">
            <a href="index.php" class="logo">
                <div class="logo-icon"><i class="fas fa-heart"></i></div>
                <div class="logo-text">Solidarité<span>Connect</span></div>
            </a>

            <div class="user-section">
                <div class="user-pill dropdown-container">
                    <div class="user-avatar" style="overflow: hidden;">
                        <?php if (!empty($assoc['logo'])): ?>
                            <img src="uploads/logos/<?= $assoc['logo'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <?= strtoupper(substr($assoc['association_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($assoc['association_name']) ?></span>
                        <span class="user-role">Association</span>
                    </div>
                </div>
                <a href="profil.php" class="logout-btn" title="Mon Profil" style="background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.2); color: var(--primary-600); margin-left: 0.5rem;">
                    <i class="fas fa-user-cog"></i>
                </a>
                <a href="logout.php" class="logout-btn" title="Déconnexion">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main -->
    <main class="dash-main">
        <!-- Welcome Banner -->
        <section class="welcome-banner">
            <div class="welcome-content">
                <div class="welcome-text">
                    <h1>Bienvenue, <?= htmlspecialchars($assoc['association_name']) ?> 👋</h1>
                    <p>Gérez vos besoins et connectez-vous avec des donateurs généreux.</p>
                </div>
                <a href="ajouter_besoin.php" class="add-btn">
                    <i class="fas fa-plus-circle"></i> Publier un besoin
                </a>
            </div>
        </section>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-top">
                    <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                </div>
                <p class="stat-value"><?= $total_needs ?></p>
                <p class="stat-label">Total des besoins</p>
            </div>
            <div class="stat-card urgent">
                <div class="stat-top">
                    <div class="stat-icon"><i class="fas fa-fire-flame-curved"></i></div>
                </div>
                <p class="stat-value"><?= $urgent_needs ?></p>
                <p class="stat-label">Besoins urgents</p>
            </div>
            <div class="stat-card satisfied">
                <div class="stat-top">
                    <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
                </div>
                <p class="stat-value"><?= $satisfied_needs ?></p>
                <p class="stat-label">Besoins satisfaits</p>
            </div>
        </div>

        <!-- Needs Table -->
        <section class="table-section">
            <div class="table-header">
                <h2 class="table-title">
                    <i class="fas fa-box-open"></i> Mes publications
                </h2>
            </div>

            <?php if(empty($needs)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3 class="empty-title">Aucun besoin publié</h3>
                    <p class="empty-text">Commencez par publier votre premier besoin pour recevoir de l'aide.</p>
                    <a href="ajouter_besoin.php" class="empty-btn">
                        <i class="fas fa-plus"></i> Publier un besoin
                    </a>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Quantité</th>
                                <th>Localisation</th>
                                <th>Date limite</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($needs as $need): ?>
                            <tr>
                                <td data-label="Titre"><span class="need-title"><?= htmlspecialchars($need['title']) ?></span></td>
                                <td data-label="Type">
                                    <span class="type-badge">
                                        <i class="fas fa-tag"></i> <?= htmlspecialchars($need['type_name']) ?>
                                    </span>
                                </td>
                                <td data-label="Quantité">
                                    <span style="font-weight: 600; color: var(--gray-700);">
                                        <?= htmlspecialchars($need['quantity'] ?? 'Non spécifié') ?>
                                    </span>
                                </td>
                                <td data-label="Localisation">
                                    <span style="color: var(--gray-600); display: flex; align-items: center; gap: 0.375rem;">
                                        <i class="fas fa-map-marker-alt" style="color: var(--primary-500);"></i>
                                        <?= htmlspecialchars($need['location'] ?? 'Non spécifié') ?>
                                    </span>
                                </td>
                                <td data-label="Date limite">
                                    <?php if(!empty($need['deadline'])): ?>
                                        <span style="color: var(--gray-600); display: flex; align-items: center; gap: 0.375rem;">
                                            <i class="fas fa-calendar" style="color: var(--danger);"></i>
                                            <?= date('d/m/Y', strtotime($need['deadline'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--gray-400); font-style: italic;">Aucune</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Statut">
                                    <span class="status-pill <?= $need['status'] ?>">
                                        <?php if($need['status'] === 'urgent'): ?>
                                            <i class="fas fa-fire"></i>
                                        <?php elseif($need['status'] === 'satisfied'): ?>
                                            <i class="fas fa-check"></i>
                                        <?php else: ?>
                                            <i class="fas fa-clock"></i>
                                        <?php endif; ?>
                                        <?= $need['status'] ?>
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <div class="actions">
                                        <a href="modifier_besoin.php?id=<?= $need['id'] ?>" class="action-btn edit" title="Modifier">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="supprimer_besoin.php?id=<?= $need['id'] ?>" class="action-btn delete" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce besoin ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
        // Auto-hide alert after 5 seconds
        const alert = document.getElementById('alert');
        if (alert) {
            setTimeout(() => {
                alert.style.animation = 'slideInRight 0.4s ease-out reverse';
                setTimeout(() => alert.remove(), 400);
            }, 5000);
        }
    </script>
</body>
</html>
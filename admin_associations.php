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

// Construire la requête
$query = "SELECT a.*, u.email, u.name as user_name, r.name as region_name,
          (SELECT COUNT(*) FROM needs WHERE association_id = a.id) as needs_count
          FROM associations a 
          JOIN users u ON a.user_id = u.id 
          LEFT JOIN regions r ON a.region_id = r.id
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (a.association_name LIKE :search OR u.email LIKE :search OR a.address LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($status_filter === 'validated') {
    $query .= " AND a.is_validated = 1";
} elseif ($status_filter === 'pending') {
    $query .= " AND a.is_validated = 0";
}

$query .= " ORDER BY a.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$associations = $stmt->fetchAll();

// Stats
$count_all = $pdo->query("SELECT COUNT(*) FROM associations")->fetchColumn();
$count_validated = $pdo->query("SELECT COUNT(*) FROM associations WHERE is_validated = 1")->fetchColumn();
$count_pending = $pdo->query("SELECT COUNT(*) FROM associations WHERE is_validated = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Associations - Admin</title>
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            min-width: 750px;
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
            flex-shrink: 0;
        }
        .assoc-name { 
            font-weight: 600; 
            color: #1e293b;
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
        }
        .action-btn.view { background: #dbeafe; color: #0284c7; }
        .action-btn.view:hover { background: #0284c7; color: white; }
        .action-btn.approve { background: #dcfce7; color: #16a34a; }
        .action-btn.approve:hover { background: #16a34a; color: white; }
        .action-btn.reject { background: #fee2e2; color: #dc2626; }
        .action-btn.reject:hover { background: #dc2626; color: white; }

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
        }
        .modal.active { display: flex; }
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
            margin-bottom: 0.5rem;
        }
        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
        }
        .needs-section {
            margin-top: 2rem;
        }
        .needs-title {
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
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
        }
        .need-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        .need-title-text {
            font-weight: 600;
            font-size: 1rem;
            color: #1e293b;
        }
        .need-desc {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .need-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.8125rem;
            color: #475569;
        }
        .need-meta-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        .need-meta-item i { color: #10b981; }
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #64748b;
        }
        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
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
            <a href="admin_associations.php" class="nav-item active">
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

    <!-- Main -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Gestion des Associations</h1>
            <p class="page-subtitle">Gérer et valider les associations inscrites</p>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-label">Total</div>
                <div class="stat-value"><?= $count_all ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Validées</div>
                <div class="stat-value" style="color: #10b981;"><?= $count_validated ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">En Attente</div>
                <div class="stat-value" style="color: #f59e0b;"><?= $count_pending ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <input type="text" class="search-input" id="searchInput" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
            <select class="filter-select" id="statusFilter">
                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Tous</option>
                <option value="validated" <?= $status_filter === 'validated' ? 'selected' : '' ?>>Validées</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>En attente</option>
            </select>
            <button class="filter-btn" onclick="applyFilters()">Filtrer</button>
            <button class="export-btn" onclick="window.location.href='export_associations.php'">
                <i class="fas fa-file-csv"></i> CSV
            </button>
            <button class="export-btn" onclick="window.open('export_associations_pdf.php', '_blank')" style="background: white; color: #ef4444; border-color: #ef4444;">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Association</th>
                            <th>Région</th>
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
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;" title="<?= htmlspecialchars($assoc['email']) ?>">
                                            <?= htmlspecialchars(substr($assoc['email'], 0, 25)) ?><?= strlen($assoc['email']) > 25 ? '...' : '' ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($assoc['region_name'] ?? 'N/A') ?></td>
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
                            <td><strong><?= $assoc['needs_count'] ?></strong></td>
                            <td>
                                <div class="action-btns">
                                    <button class="action-btn view" onclick="viewAssociation(<?= $assoc['id'] ?>)" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if(!$assoc['is_validated']): ?>
                                        <button class="action-btn approve" onclick="if(confirm('Approuver?')) window.location.href='valider_assoc.php?id=<?= $assoc['id'] ?>'" title="Approuver">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="action-btn reject" onclick="if(confirm('Rejeter?')) window.location.href='rejeter_assoc.php?id=<?= $assoc['id'] ?>'" title="Rejeter">
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
        </div>
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
            window.location.href = `admin_associations.php?search=${encodeURIComponent(search)}&status=${status}`;
        }
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') applyFilters();
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
                                        <div class="need-title-text">${need.title}</div>
                                        <span class="status-badge ${need.status}">
                                            ${need.status === 'urgent' ? '<i class="fas fa-fire"></i> Urgent' : 
                                              need.status === 'normal' ? '<i class="fas fa-clock"></i> Normal' : 
                                              '<i class="fas fa-check"></i> Satisfait'}
                                        </span>
                                    </div>
                                    <div class="need-desc">${need.description}</div>
                                    <div class="need-meta">
                                        <span class="need-meta-item">
                                            <i class="fas fa-tag"></i> ${need.type_name}
                                        </span>
                                        ${need.quantity ? `<span class="need-meta-item"><i class="fas fa-box"></i> ${need.quantity}</span>` : ''}
                                        ${need.location ? `<span class="need-meta-item"><i class="fas fa-map-marker-alt"></i> ${need.location}</span>` : ''}
                                        ${need.deadline ? `<span class="need-meta-item"><i class="fas fa-calendar"></i> ${need.deadline}</span>` : ''}
                                    </div>
                                </div>
                            `).join('');
                        } else {
                            needsHtml = '<div class="empty-state"><div class="empty-icon"><i class="fas fa-inbox"></i></div><p>Aucun besoin publié</p></div>';
                        }

                        document.getElementById('modalBody').innerHTML = `
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">Nom</div>
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
                                    <div class="info-label">Région</div>
                                    <div class="info-value">${assoc.region_name || 'N/A'}</div>
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
                            </div>
                            
                            <div class="needs-section">
                                <h4 class="needs-title">
                                    <i class="fas fa-clipboard-list"></i>
                                    Besoins (${needs.length})
                                </h4>
                                ${needsHtml}
                            </div>

                            ${!assoc.is_validated ? `
                                <div class="modal-actions">
                                    <button class="modal-btn approve" onclick="if(confirm('Approuver cette association?')) window.location.href='valider_assoc.php?id=${assoc.id}'">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                    <button class="modal-btn reject" onclick="if(confirm('Rejeter cette association?')) window.location.href='rejeter_assoc.php?id=${assoc.id}'">
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

        document.getElementById('associationModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        function exportData() {
            // Function removed - using direct links now
        }
    </script>
</body>
</html>

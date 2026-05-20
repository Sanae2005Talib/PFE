<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'association') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: association_dashboard.php");
    exit();
}

$id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT n.* FROM needs n
    JOIN associations a ON n.association_id = a.id
    WHERE n.id = ? AND a.user_id = ?
");
$stmt->execute([$id, $_SESSION['user_id']]);
$besoin = $stmt->fetch();

if (!$besoin) {
    die("Besoin non trouvé ou accès refusé.");
}

$types = $pdo->query("SELECT * FROM donation_types")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Besoin - Solidarité Connect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .alert {
            position: fixed;
            top: 2rem;
            right: 2rem;
            max-width: 400px;
            padding: 1rem 1.25rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-2xl);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(220, 38, 38, 0.95));
            color: var(--white);
        }

        .alert i { font-size: 1.25rem; }
        .alert-message { font-weight: 600; font-size: 0.9375rem; }

        body {
            background: linear-gradient(135deg, var(--secondary-50) 0%, var(--primary-50) 100%);
            min-height: 100vh;
            padding: 4rem 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 650px;
            width: 100%;
            position: relative;
            z-index: 10;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 2rem;
            transition: all var(--transition-base);
        }

        .back-link:hover {
            color: var(--secondary-600);
            transform: translateX(-5px);
        }

        .form-card {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: var(--radius-3xl);
            padding: 3.5rem;
            box-shadow: var(--shadow-2xl);
            border: 1px solid var(--glass-border);
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--gradient-secondary);
        }

        .form-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .form-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-secondary);
            border-radius: var(--radius-2xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: var(--white);
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.3);
        }

        .form-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-900);
        }

        .form-subtitle {
            color: var(--gray-500);
            font-size: 1rem;
            margin-top: 0.5rem;
        }

        /* Status Options Premium */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .status-card {
            position: relative;
        }

        .status-card input {
            position: absolute;
            opacity: 0;
        }

        .status-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 0.5rem;
            background: var(--white);
            border: 2px solid var(--gray-100);
            border-radius: var(--radius-xl);
            cursor: pointer;
            transition: all var(--transition-base);
            text-align: center;
        }

        .status-box i {
            font-size: 1.25rem;
            color: var(--gray-400);
        }

        .status-box span {
            font-weight: 700;
            font-size: 0.8125rem;
            color: var(--gray-600);
        }

        .status-card input:checked + .status-box.normal {
            border-color: var(--primary-500);
            background: var(--primary-50);
        }
        .status-card input:checked + .status-box.normal i,
        .status-card input:checked + .status-box.normal span { color: var(--primary-600); }

        .status-card input:checked + .status-box.urgent {
            border-color: var(--danger);
            background: #FEE2E2;
        }
        .status-card input:checked + .status-box.urgent i,
        .status-card input:checked + .status-box.urgent span { color: var(--danger); }

        .status-card input:checked + .status-box.satisfied {
            border-color: var(--success);
            background: #DCFCE7;
        }
        .status-card input:checked + .status-box.satisfied i,
        .status-card input:checked + .status-box.satisfied span { color: var(--success); }

        .status-box:hover {
            transform: translateY(-3px);
            border-color: var(--gray-300);
        }

        .submit-btn-premium {
            width: 100%;
            margin-top: 1rem;
            background: var(--gradient-secondary) !important;
            box-shadow: 0 6px 25px rgba(14, 165, 233, 0.4) !important;
        }

        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 1.25rem;
            color: var(--gray-500);
            font-weight: 600;
            text-decoration: none;
            transition: color var(--transition-base);
            font-size: 0.875rem;
        }

        .cancel-link:hover { color: var(--gray-800); }
    </style>
</head>
<body>
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error" id="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="alert-message">
                <?php 
                if($_GET['error'] === 'champs_vides') echo 'Veuillez remplir tous les champs obligatoires.';
                elseif($_GET['error'] === 'modification_echouee') echo 'Erreur lors de la modification.';
                else echo 'Une erreur est survenue.';
                ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">
        <a href="association_dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>

        <div class="form-card">
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-pen-to-square"></i>
                </div>
                <h1 class="form-title">Modifier</h1>
                <p class="form-subtitle">Mettez à jour votre annonce</p>
            </div>

            <form action="traitement_update.php" method="POST" class="form-content">
                <input type="hidden" name="id" value="<?= $besoin['id'] ?>">

                <div class="form-group">
                    <label class="form-label">Titre de l'annonce</label>
                    <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($besoin['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Catégorie</label>
                    <select name="donation_type_id" class="form-select" required>
                        <?php foreach($types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($t['id'] == $besoin['donation_type_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Quantité souhaitée</label>
                    <input type="text" name="quantity" class="form-input" value="<?= htmlspecialchars($besoin['quantity'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Localisation</label>
                    <input type="text" name="location" class="form-input" value="<?= htmlspecialchars($besoin['location'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Date limite (optionnelle)</label>
                    <input type="date" name="deadline" class="form-input" value="<?= $besoin['deadline'] ?? '' ?>" min="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Statut actuel</label>
                    <div class="status-grid">
                        <label class="status-card">
                            <input type="radio" name="status" value="normal" <?= ($besoin['status'] == 'normal') ? 'checked' : '' ?>>
                            <div class="status-box normal">
                                <i class="fas fa-clock"></i>
                                <span>Normal</span>
                            </div>
                        </label>
                        <label class="status-card">
                            <input type="radio" name="status" value="urgent" <?= ($besoin['status'] == 'urgent') ? 'checked' : '' ?>>
                            <div class="status-box urgent">
                                <i class="fas fa-fire-flame-curved"></i>
                                <span>Urgent</span>
                            </div>
                        </label>
                        <label class="status-card">
                            <input type="radio" name="status" value="satisfied" <?= ($besoin['status'] == 'satisfied') ? 'checked' : '' ?>>
                            <div class="status-box satisfied">
                                <i class="fas fa-circle-check"></i>
                                <span>Satisfait</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" required><?= htmlspecialchars($besoin['description']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg submit-btn-premium">
                    <i class="fas fa-save"></i> <span>Enregistrer les changements</span>
                </button>

                <a href="association_dashboard.php" class="cancel-link">Annuler les modifications</a>
            </form>
        </div>
    </div>

    <script>
        const alert = document.getElementById('alert');
        if (alert) {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(400px)';
                setTimeout(() => alert.remove(), 300);
            }, 4000);
        }
    </script>
</body>
</html>
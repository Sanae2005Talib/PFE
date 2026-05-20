<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'association') {
    header("Location: login.php");
    exit();
}

$types = $pdo->query("SELECT * FROM donation_types")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier un Besoin - Solidarité Connect</title>
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
            background: linear-gradient(135deg, var(--primary-50) 0%, var(--secondary-50) 100%);
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
            color: var(--primary-600);
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
            background: var(--gradient-primary);
        }

        .form-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .form-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-primary);
            border-radius: var(--radius-2xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: var(--white);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
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

        /* Priority Options Premium */
        .priority-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .priority-card {
            position: relative;
        }

        .priority-card input {
            position: absolute;
            opacity: 0;
        }

        .priority-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1.25rem;
            background: var(--white);
            border: 2px solid var(--gray-100);
            border-radius: var(--radius-xl);
            cursor: pointer;
            transition: all var(--transition-base);
            text-align: center;
        }

        .priority-box i {
            font-size: 1.5rem;
            color: var(--gray-400);
        }

        .priority-box span {
            font-weight: 700;
            font-size: 0.9375rem;
            color: var(--gray-600);
        }

        .priority-card input:checked + .priority-box.normal {
            border-color: var(--primary-500);
            background: var(--primary-50);
        }
        .priority-card input:checked + .priority-box.normal i,
        .priority-card input:checked + .priority-box.normal span {
            color: var(--primary-600);
        }

        .priority-card input:checked + .priority-box.urgent {
            border-color: var(--danger);
            background: #FEE2E2;
        }
        .priority-card input:checked + .priority-box.urgent i,
        .priority-card input:checked + .priority-box.urgent span {
            color: var(--danger);
        }

        .priority-box:hover {
            transform: translateY(-3px);
            border-color: var(--gray-300);
        }

        .submit-btn-premium {
            width: 100%;
            margin-top: 1rem;
        }

        /* Tips */
        .premium-tips {
            margin-top: 2.5rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary-50), var(--secondary-50));
            border-radius: var(--radius-2xl);
            border: 1px solid rgba(16, 185, 129, 0.1);
        }

        .tip-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .tip-item i {
            color: var(--primary-600);
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error" id="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="alert-message">
                <?php 
                if($_GET['error'] === 'champs_vides') echo 'Veuillez remplir tous les champs obligatoires.';
                elseif($_GET['error'] === 'ajout_echoue') echo 'Erreur lors de l\'ajout du besoin.';
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
                    <i class="fas fa-plus"></i>
                </div>
                <h1 class="form-title">Nouveau besoin</h1>
                <p class="form-subtitle">Que recherchez-vous pour votre association ?</p>
            </div>

            <form action="traitement_besoin.php" method="POST" class="form-content">
                <div class="form-group">
                    <label class="form-label">Titre de l'annonce</label>
                    <input type="text" name="title" class="form-input" placeholder="Ex: Couvertures pour l'hiver" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Catégorie de don</label>
                    <select name="donation_type_id" class="form-select" required>
                        <option value="">Sélectionner une catégorie...</option>
                        <?php foreach($types as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Niveau d'urgence</label>
                    <div class="priority-grid">
                        <label class="priority-card">
                            <input type="radio" name="status" value="normal" checked>
                            <div class="priority-box normal">
                                <i class="fas fa-clock"></i>
                                <span>Normal</span>
                            </div>
                        </label>
                        <label class="priority-card">
                            <input type="radio" name="status" value="urgent">
                            <div class="priority-box urgent">
                                <i class="fas fa-fire-flame-curved"></i>
                                <span>Urgent</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Quantité souhaitée</label>
                    <input type="text" name="quantity" class="form-input" placeholder="Ex: 50 unités, 10 kg, 5 cartons..." required>
                </div>

                <div class="form-group">
                    <label class="form-label">Localisation</label>
                    <input type="text" name="location" class="form-input" placeholder="Ex: Casablanca, Rabat, Marrakech..." required>
                </div>

                <div class="form-group">
                    <label class="form-label">Date limite (optionnelle)</label>
                    <input type="date" name="deadline" class="form-input" min="<?= date('Y-m-d') ?>">
                    <small style="color: var(--gray-500); font-size: 0.875rem; margin-top: 0.5rem; display: block;">
                        <i class="fas fa-info-circle"></i> Laissez vide si pas de date limite
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Description précise</label>
                    <textarea name="description" class="form-textarea" placeholder="Détaillez votre besoin (état recherché, conditions...)" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg submit-btn-premium">
                    <i class="fas fa-paper-plane"></i> <span>Publier l'annonce</span>
                </button>

                <div class="premium-tips">
                    <div class="tip-item">
                        <i class="fas fa-lightbulb"></i>
                        <p><strong>Conseil :</strong> Soyez le plus précis possible pour aider les donateurs à comprendre votre besoin exact.</p>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <p><strong>Localisation :</strong> Indiquez votre ville pour faciliter les dons locaux.</p>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-calendar-check"></i>
                        <p><strong>Date limite :</strong> Ajoutez une date si votre besoin est urgent ou saisonnier.</p>
                    </div>
                </div>
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
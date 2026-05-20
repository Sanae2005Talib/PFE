<?php include_once 'api/conn.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Donateur - Solidarité Connect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, var(--secondary-50) 0%, var(--primary-50) 100%);
            min-height: 100vh;
            padding: 4rem 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 500px;
            width: 100%;
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
            background: var(--white);
            border-radius: var(--radius-3xl);
            padding: 3rem;
            box-shadow: var(--shadow-2xl);
            border: 1px solid var(--glass-border);
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
            margin-bottom: 2.5rem;
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

        .form-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-100);
        }

        .form-footer p {
            color: var(--gray-500);
            font-size: 0.9375rem;
        }

        .form-footer a {
            color: var(--primary-600);
            font-weight: 700;
            text-decoration: none;
        }

        .form-footer a:hover {
            color: var(--primary-700);
        }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour à l'accueil
        </a>

        <div class="form-card" style="position: relative;">
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1 class="form-title">Devenir Donateur</h1>
                <p class="form-subtitle">Créez votre compte pour aider les associations</p>
            </div>

            <form action="traitement_inscription_citizen.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Votre nom complet</label>
                    <input type="text" name="name" class="form-input" placeholder="Ex: Ahmed Bennani" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="votre@email.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" minlength="6" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirm" class="form-input" placeholder="••••••••" minlength="6" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-rocket"></i> <span>Créer mon compte</span>
                </button>

                <div class="form-footer">
                    <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
                    <p style="margin-top: 0.5rem;">Vous êtes une association ? <a href="inscription.php">S'inscrire ici</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

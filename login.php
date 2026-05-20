<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Solidarité Connect</title>
    <meta name="description" content="Connectez-vous à votre espace association">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary-50) 0%, var(--secondary-50) 50%, var(--white) 100%);
            position: relative;
        }

        /* Animated Background Grid */
        .bg-grid {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(16, 185, 129, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(16, 185, 129, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
        }

        /* Decorative Elements */
        .deco {
            position: fixed;
            border-radius: 50%;
            opacity: 0.5;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
        }

        .deco-1 {
            width: 500px;
            height: 500px;
            background: var(--primary-300);
            top: -200px;
            left: -200px;
            animation: pulse-deco 8s ease-in-out infinite;
        }

        .deco-2 {
            width: 400px;
            height: 400px;
            background: var(--secondary-300);
            bottom: -150px;
            right: -150px;
            animation: pulse-deco 10s ease-in-out infinite reverse;
        }

        @keyframes pulse-deco {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.6; }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-500);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            transition: all var(--transition-base);
        }

        .back-link:hover {
            color: var(--primary-600);
            gap: 0.75rem;
        }

        /* Login Card */
        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: var(--radius-3xl);
            padding: 2.5rem 2.5rem 2rem;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-2xl);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--gradient-primary);
        }

        /* Logo Section */
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-circle {
            width: 70px;
            height: 70px;
            background: var(--gradient-primary);
            border-radius: var(--radius-2xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.75rem;
            color: var(--white);
            box-shadow: 0 10px 35px rgba(16, 185, 129, 0.4);
            position: relative;
        }

        .logo-circle::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: calc(var(--radius-2xl) + 4px);
            background: var(--gradient-primary);
            z-index: -1;
            opacity: 0.4;
            filter: blur(15px);
        }

        .logo-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gray-900);
        }

        .logo-title span {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-subtitle {
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-top: 0.375rem;
            font-weight: 500;
        }

        /* Form */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1rem;
            transition: color var(--transition-base);
            pointer-events: none;
        }

        .input-group .form-input {
            padding-left: 3.5rem;
        }

        .input-group .form-input:focus ~ .input-icon,
        .input-group .form-input:not(:placeholder-shown) ~ .input-icon {
            color: var(--primary-500);
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: var(--gradient-primary);
            color: var(--white);
            border: none;
            border-radius: var(--radius-xl);
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all var(--transition-base);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            box-shadow: 0 6px 25px rgba(16, 185, 129, 0.4);
            font-family: inherit;
            margin-top: 0.25rem;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 35px rgba(16, 185, 129, 0.5);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-500);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            transition: all var(--transition-base);
        }

        .back-link:hover {
            color: var(--primary-600);
            gap: 0.75rem;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.25rem 0 1rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gray-200);
        }

        .divider span {
            color: var(--gray-400);
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Footer */
        .login-footer {
            text-align: center;
        }

        .register-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-600);
            font-weight: 700;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all var(--transition-base);
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-lg);
            background: var(--primary-50);
        }

        .register-link:hover {
            background: var(--primary-100);
            gap: 0.625rem;
        }

        /* Trust Badges */
        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--gray-100);
        }

        .trust-badge {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            color: var(--gray-400);
            font-size: 0.6875rem;
            font-weight: 600;
        }

        .trust-badge i {
            color: var(--primary-500);
            font-size: 0.75rem;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 2rem;
            }

            .trust-badges {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 0.875rem;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(239, 68, 68, 0.2);
            font-size: 0.8125rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-bottom: 1.25rem;
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }

        .success-message {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            padding: 0.875rem;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(16, 185, 129, 0.2);
            font-size: 0.8125rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-bottom: 1.25rem;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        .form-input.is-invalid {
            border-color: #ef4444;
            background-color: rgba(239, 68, 68, 0.02);
        }

        .form-input.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }
    </style>
</head>
<body>
    <!-- Background -->
    <div class="bg-grid"></div>
    <div class="deco deco-1"></div>
    <div class="deco deco-2"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="login-container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour à l'accueil
        </a>

        <div class="login-card">
            <div class="login-logo">
                <div class="logo-circle">
                    <i class="fas fa-lock"></i>
                </div>
                <h1 class="logo-title">Solidarité<span>Connect</span></h1>
                <p class="logo-subtitle">Connectez-vous pour gérer vos besoins</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    Email ou mot de passe incorrect. Veuillez réessayer.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    Votre inscription a été réussie ! Vous pouvez vous connecter.
                </div>
            <?php endif; ?>

            <form action="traitement_login.php" method="POST" class="login-form">
                <div class="form-group">
                    <label class="form-label">Adresse email</label>
                    <div class="input-group">
                        <input type="email" name="email" class="form-input <?php echo isset($_GET['error']) ? 'is-invalid' : ''; ?>" placeholder="votre@email.com" required>
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-input <?php echo isset($_GET['error']) ? 'is-invalid' : ''; ?>" placeholder="••••••••" required>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>

            <div class="divider">
                <span>Nouveau ici ?</span>
            </div>

            <div class="login-footer">
                <a href="inscription.php" class="register-link">
                    <i class="fas fa-building"></i> Inscrire mon association <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="trust-badges">
                <div class="trust-badge">
                    <i class="fas fa-shield-alt"></i> Connexion sécurisée
                </div>
                <div class="trust-badge">
                    <i class="fas fa-check-circle"></i> 100% gratuit
                </div>
            </div>
        </div>
    </div>
</body>
</html>
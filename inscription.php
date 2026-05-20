<?php include_once 'api/conn.php'; $regions = $pdo->query("SELECT * FROM regions ORDER BY name ASC")->fetchAll(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejoindre Solidarité Connect - Inscription</title>
    <meta name="description" content="Inscrivez votre association sur Solidarité Connect">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, var(--primary-50) 0%, var(--secondary-50) 50%, var(--white) 100%);
        }

        /* Split Layout */
        .split-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Left Panel - Visual */
        .visual-panel {
            width: 45%;
            background: var(--gradient-primary);
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .visual-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 10% 20%, rgba(255, 255, 255, 0.2) 0%, transparent 50%),
                radial-gradient(ellipse at 90% 80%, rgba(14, 165, 233, 0.3) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 100%, rgba(16, 185, 129, 0.2) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Animated Shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            background: var(--white);
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
            animation: float 15s ease-in-out infinite;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: 10%;
            right: -50px;
            animation: float 20s ease-in-out infinite reverse;
        }

        .shape-3 {
            width: 150px;
            height: 150px;
            bottom: 30%;
            left: 20%;
            animation: float 18s ease-in-out infinite 3s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(20px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        .visual-content {
            position: relative;
            z-index: 1;
            max-width: 420px;
        }

        .visual-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 3rem;
        }

        .visual-logo .logo-icon {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .visual-logo .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--white);
        }

        .visual-title {
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--white);
            line-height: 1.15;
            margin-bottom: 1.5rem;
        }

        .visual-subtitle {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.7;
            margin-bottom: 3rem;
        }

        /* Features */
        .features {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 1.25rem;
            border-radius: var(--radius-xl);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all var(--transition-base);
        }

        .feature:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(8px);
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .feature-text {
            color: var(--white);
            font-weight: 600;
            font-size: 0.9375rem;
        }

        /* Right Panel - Form */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 3rem;
            position: relative;
        }

        .form-container {
            width: 100%;
            max-width: 480px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-500);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 2rem;
            transition: all var(--transition-base);
        }

        .back-link:hover {
            color: var(--primary-600);
            gap: 0.75rem;
        }

        .form-header {
            margin-bottom: 2.5rem;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .form-title span {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-subtitle {
            color: var(--gray-500);
            font-size: 1rem;
        }

        /* Form Sections */
        .form-section {
            margin-bottom: 2rem;
        }

        .section-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary-600);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary-100);
        }

        .section-label i {
            font-size: 0.875rem;
        }

        /* Form Grid */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 1.25rem;
            background: var(--gradient-primary);
            color: var(--white);
            border: none;
            border-radius: var(--radius-xl);
            font-size: 1.0625rem;
            font-weight: 700;
            cursor: pointer;
            transition: all var(--transition-base);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 6px 25px rgba(16, 185, 129, 0.4);
            font-family: inherit;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 35px rgba(16, 185, 129, 0.5);
        }

        .submit-btn i {
            font-size: 1.125rem;
        }

        /* Footer */
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
            transition: color var(--transition-base);
        }

        .form-footer a:hover {
            color: var(--primary-700);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .visual-panel {
                display: none;
            }
            .form-panel {
                padding: 2rem;
            }
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="split-layout">
        <!-- Visual Panel -->
        <div class="visual-panel">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            
            <div class="visual-content">
                <div class="visual-logo">
                    <div class="logo-icon"><i class="fas fa-heart"></i></div>
                    <div class="logo-text">SolidaritéConnect</div>
                </div>
                
                <h1 class="visual-title">Rejoignez notre communauté solidaire</h1>
                <p class="visual-subtitle">
                    Connectez-vous avec des donateurs généreux à travers tout le Maroc et recevez l'aide dont votre association a besoin.
                </p>
                
                <div class="features">
                    <div class="feature">
                        <div class="feature-icon"><i class="fas fa-bullhorn"></i></div>
                        <span class="feature-text">Publiez vos besoins gratuitement</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fas fa-users"></i></div>
                        <span class="feature-text">Touchez des milliers de donateurs</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                        <span class="feature-text">Suivez vos besoins en temps réel</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <span class="feature-text">100% gratuit et sécurisé</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Panel -->
        <div class="form-panel">
            <div class="form-container">
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Retour à l'accueil
                </a>

                <div class="form-header">
                    <h2 class="form-title">Créer un <span>compte</span></h2>
                    <p class="form-subtitle">Remplissez le formulaire pour inscrire votre association</p>
                </div>

                <form action="traitement_inscription.php" method="POST">
                    <!-- Personal Info -->
                    <div class="form-section">
                        <div class="section-label">
                            <i class="fas fa-user"></i> Informations personnelles
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Votre nom</label>
                                <input type="text" name="user_name" class="form-input" placeholder="Prénom Nom" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-input" placeholder="votre@email.com" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <!-- Association Info -->
                    <div class="form-section">
                        <div class="section-label">
                            <i class="fas fa-building"></i> Votre association
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nom de l'association</label>
                            <input type="text" name="assoc_name" class="form-input" placeholder="Association..." required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Téléphone</label>
                                <input type="tel" name="phone" class="form-input" placeholder="+212 6XX XXX XXX" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Région</label>
                                <select name="region_id" class="form-input form-select" required>
                                    <option value="">Sélectionner...</option>
                                    <?php foreach($regions as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Adresse complète</label>
                            <textarea name="address" class="form-input form-textarea" rows="3" placeholder="Numéro, rue, ville..." required></textarea>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-rocket"></i> Créer mon compte
                    </button>

                    <div class="form-footer">
                        <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
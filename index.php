<?php
include_once 'api/conn.php';
$regions = $pdo->query("SELECT * FROM regions ORDER BY name ASC")->fetchAll();
$types = $pdo->query("SELECT * FROM donation_types")->fetchAll();

$where = " WHERE n.status != 'satisfied' AND a.is_validated = 1";
$params = [];

if (!empty($_GET['region'])) {
    $where .= " AND a.region_id = :region_id";
    $params[':region_id'] = $_GET['region'];
}

if (!empty($_GET['type'])) {
    $where .= " AND n.donation_type_id = :type_id";
    $params[':type_id'] = $_GET['type'];
}

if (!empty($_GET['search'])) {
    $where .= " AND (n.title LIKE :search OR n.description LIKE :search)";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

$sql = "SELECT n.*, a.association_name, a.phone, r.name as region_name, t.name as type_name 
        FROM needs n JOIN associations a ON n.association_id = a.id
        JOIN regions r ON a.region_id = r.id JOIN donation_types t ON n.donation_type_id = t.id
        $where ORDER BY (n.status = 'urgent') DESC, n.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all_needs = $stmt->fetchAll();

$count_assoc = $pdo->query("SELECT COUNT(*) FROM associations WHERE is_validated = 1")->fetchColumn();
$count_needs = $pdo->query("SELECT COUNT(*) FROM needs n JOIN associations a ON n.association_id = a.id WHERE a.is_validated = 1")->fetchColumn();
$count_satisfied = $pdo->query("SELECT COUNT(*) FROM needs WHERE status = 'satisfied'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solidarité Connect - Ensemble pour aider ceux qui en ont besoin</title>
    <meta name="description" content="Plateforme marocaine qui connecte les associations aux citoyens généreux. Découvrez les besoins réels et faites un don utile.">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Steps Section */
        .steps-section {
            background: var(--white);
        }
        
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2.5rem;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .step {
            text-align: center;
            padding: 2.5rem 2rem;
            position: relative;
        }
        
        .step-icon-wrap {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }
        
        .step-icon {
            width: 90px;
            height: 90px;
            background: var(--gradient-hero);
            border-radius: var(--radius-2xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary-600);
            box-shadow: var(--shadow-lg);
            transition: all var(--transition-base);
        }
        
        .step:hover .step-icon {
            transform: translateY(-5px) scale(1.05);
            box-shadow: var(--shadow-xl), var(--shadow-glow-primary);
        }
        
        .step-num {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 32px;
            height: 32px;
            background: var(--gradient-primary);
            color: var(--white);
            border-radius: 50%;
            font-size: 0.875rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);
        }
        
        .step-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.625rem;
        }
        
        .step-desc {
            font-size: 1rem;
            color: var(--gray-500);
            line-height: 1.6;
        }
        
        /* Connector Lines */
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -1.25rem;
            width: 2.5rem;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-300), var(--secondary-300));
            border-radius: var(--radius-full);
        }

        /* Types Section */
        .types-section {
            background: linear-gradient(180deg, var(--gray-50) 0%, var(--white) 100%);
        }
        
        .types-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
            max-width: 1100px;
            margin: 0 auto;
        }
        
        .type-card {
            background: var(--white);
            padding: 2rem 1.5rem;
            border-radius: var(--radius-2xl);
            text-align: center;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-100);
            transition: all var(--transition-base);
            cursor: default;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        
        .type-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-200);
        }
        
        .type-icon {
            width: 70px;
            height: 70px;
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-base);
        }
        
        .type-card:hover .type-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .type-icon i {
            font-size: 2rem;
        }
        
        .type-name {
            font-weight: 700;
            color: var(--gray-800);
            font-size: 1.0625rem;
        }

        /* Needs Section */
        .needs-section {
            background: linear-gradient(180deg, var(--white) 0%, var(--primary-50) 100%);
        }
        
        /* Search Bar Premium */
        .search-container {
            max-width: 700px;
            margin: 0 auto 4rem;
        }
        
        .search-bar {
            background: var(--white);
            padding: 0.75rem;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(0, 0, 0, 0.05);
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 0.75rem;
        }

        .search-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input-group i {
            position: absolute;
            left: 1rem;
            color: var(--gray-400);
        }

        .search-bar input, .search-bar select {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.5rem;
            border: 1px solid var(--gray-100);
            background: var(--gray-50);
            border-radius: var(--radius-xl);
            font-size: 0.9375rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            color: var(--gray-700);
            transition: all var(--transition-base);
        }

        .search-bar input:focus, .search-bar select:focus {
            outline: none;
            background: var(--white);
            border-color: var(--primary-300);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        /* Needs Grid */
        .needs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 2rem;
            max-width: 1280px;
            margin: 0 auto;
        }

        /* Empty State */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 5rem 2rem;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-3xl);
            border: 1px solid var(--glass-border);
        }
        
        .empty-state i {
            font-size: 4rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            display: block;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: var(--gray-500);
            font-size: 1rem;
        }

        @media (max-width: 900px) {
            .steps-grid {
                grid-template-columns: 1fr;
            }
            .step::after {
                display: none;
            }
        }
        
        @media (max-width: 850px) {
            .search-bar {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
            .search-bar button {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .needs-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<!-- Floating Orbs Background -->
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<!-- Navbar -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <div class="logo-icon"><i class="fas fa-heart"></i></div>
            <div class="logo-text">Solidarité<span class="hide-mobile">Connect</span></div>
        </a>
        <div class="nav-links">
            <a href="#how" class="nav-link">Comment ça marche</a>
            <a href="#besoins" class="nav-link">Les besoins</a>
            <a href="login.php" class="btn btn-outline btn-sm">Connexion</a>
            <a href="inscription.php" class="btn btn-primary btn-sm"><i class="fas fa-plus hide-mobile"></i> Inscrire <span class="hide-mobile">Association</span></a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-hand-holding-heart"></i> 
                Plateforme solidaire marocaine 🇲🇦
            </div>
            <h1 class="hero-title">
                Ensemble, aidons ceux qui en ont <span class="highlight">vraiment besoin</span>
            </h1>
            <p class="hero-subtitle">
                Solidarité Connect connecte les citoyens généreux aux associations marocaines. 
                Découvrez les besoins réels et faites un don qui compte vraiment.
            </p>
            <div class="hero-buttons">
                <a href="#besoins" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Voir les besoins
                </a>
                <a href="inscription_citizen.php" class="btn btn-glass btn-lg">
                    <i class="fas fa-hand-holding-heart"></i> Devenir donateur
                </a>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <div class="stat-number"><?= $count_assoc ?>+</div>
                    <div class="stat-label">Associations</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= $count_needs ?>+</div>
                    <div class="stat-label">Besoins publiés</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?= $count_satisfied ?>+</div>
                    <div class="stat-label">Besoins satisfaits</div>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=600&h=500&fit=crop" alt="Entraide et solidarité">
            <div class="hero-card top">
                <div class="hero-card-icon green"><i class="fas fa-box-open"></i></div>
                <div class="hero-card-text">
                    <div class="hero-card-title">Dons utiles</div>
                    <div class="hero-card-sub">Ce dont ils ont vraiment besoin</div>
                </div>
            </div>
            <div class="hero-card bottom">
                <div class="hero-card-icon blue"><i class="fas fa-hands-helping"></i></div>
                <div class="hero-card-text">
                    <div class="hero-card-title">Impact direct</div>
                    <div class="hero-card-sub">100% aux associations</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="section steps-section" id="how">
    <div class="section-header">
        <span class="section-badge"><i class="fas fa-sparkles"></i> Simple & Efficace</span>
        <h2 class="section-title">Comment ça marche ?</h2>
        <p class="section-subtitle">Trois étapes simples pour faire un don utile et avoir un impact réel</p>
    </div>
    <div class="steps-grid">
        <div class="step">
            <div class="step-icon-wrap">
                <div class="step-icon"><i class="fas fa-search"></i></div>
                <span class="step-num">1</span>
            </div>
            <h3 class="step-title">Explorez</h3>
            <p class="step-desc">Parcourez les besoins des associations près de chez vous</p>
        </div>
        <div class="step">
            <div class="step-icon-wrap">
                <div class="step-icon"><i class="fas fa-hand-pointer"></i></div>
                <span class="step-num">2</span>
            </div>
            <h3 class="step-title">Choisissez</h3>
            <p class="step-desc">Sélectionnez un besoin qui vous touche personnellement</p>
        </div>
        <div class="step">
            <div class="step-icon-wrap">
                <div class="step-icon"><i class="fas fa-phone-alt"></i></div>
                <span class="step-num">3</span>
            </div>
            <h3 class="step-title">Contactez</h3>
            <p class="step-desc">Appelez l'association pour organiser votre don</p>
        </div>
    </div>
</section>



<!-- Types -->
<section class="section types-section">
    <div class="section-header">
        <span class="section-badge"><i class="fas fa-gift"></i> Types de dons</span>
        <h2 class="section-title">Que pouvez-vous donner ?</h2>
        <p class="section-subtitle">Chaque don compte, peu importe sa nature</p>
    </div>
    <div class="types-grid">
        <?php 
        $icons=['fas fa-tshirt','fas fa-utensils','fas fa-book','fas fa-pills','fas fa-couch','fas fa-money-bill-wave'];
        $colors=['#10b981','#0ea5e9','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
        foreach($types as $i=>$t): 
            $color = $colors[$i%6];
        ?>
        <div class="type-card">
            <div class="type-icon" style="background: linear-gradient(135deg, <?=$color?>15, <?=$color?>25);">
                <i class="<?=$icons[$i%6]?>" style="color: <?=$color?>;"></i>
            </div>
            <span class="type-name"><?=htmlspecialchars($t['name'])?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>



<!-- Needs -->
<section class="section needs-section" id="besoins">
    <div class="section-header">
        <span class="section-badge"><i class="fas fa-heart"></i> Besoins actuels</span>
        <h2 class="section-title">Les associations ont besoin de vous</h2>
        <p class="section-subtitle">Trouvez un besoin et faites la différence aujourd'hui</p>
    </div>
    
    <div class="search-container">
        <form method="GET" class="search-bar">
            <div class="search-input-group">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Que cherchez-vous ?" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            
            <div class="search-input-group">
                <i class="fas fa-map-marker-alt"></i>
                <select name="region">
                    <option value="">Toutes les régions</option>
                    <?php foreach($regions as $r): ?>
                    <option value="<?=$r['id']?>" <?=(!empty($_GET['region'])&&$_GET['region']==$r['id'])?'selected':''?>><?=htmlspecialchars($r['name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="search-input-group">
                <i class="fas fa-tag"></i>
                <select name="type">
                    <option value="">Tous les types</option>
                    <?php foreach($types as $t): ?>
                    <option value="<?=$t['id']?>" <?=(!empty($_GET['type'])&&$_GET['type']==$t['id'])?'selected':''?>><?=htmlspecialchars($t['name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrer</button>
        </form>
    </div>

    <div class="needs-grid">
        <?php if(empty($all_needs)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Aucun besoin pour le moment</h3>
            <p>Revenez bientôt ou essayez une autre région</p>
        </div>
        <?php else: foreach($all_needs as $n): ?>
        <div class="need-card">
            <div class="card-top">
                <div class="card-badges">
                    <span class="badge badge-type"><i class="fas fa-tag"></i> <?=htmlspecialchars($n['type_name'])?></span>
                    <?php if($n['status']==='urgent'): ?>
                    <span class="badge badge-urgent"><i class="fas fa-fire"></i> Urgent</span>
                    <?php endif; ?>
                </div>
                <h3 class="card-title"><?=htmlspecialchars($n['title'])?></h3>
                <p class="card-desc"><?=htmlspecialchars($n['description'])?></p>
            </div>
            <div class="card-bottom">
                <div class="assoc">
                    <div class="assoc-icon"><i class="fas fa-building"></i></div>
                    <div class="assoc-info">
                        <div class="assoc-name"><?=htmlspecialchars($n['association_name'])?></div>
                        <div class="assoc-loc"><i class="fas fa-map-marker-alt"></i> <?=htmlspecialchars($n['region_name'])?></div>
                    </div>
                </div>
                <a href="tel:<?=$n['phone']?>" class="contact-btn">
                    <i class="fas fa-phone-alt"></i> Appeler
                </a>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="cta-content">
        <h2 class="cta-title">Vous êtes une association ?</h2>
        <p class="cta-subtitle">
            Inscrivez-vous gratuitement et connectez-vous avec des milliers de donateurs généreux à travers le Maroc
        </p>
        <div class="cta-buttons">
            <a href="inscription.php" class="btn btn-white btn-lg">
                <i class="fas fa-rocket"></i> S'inscrire gratuitement
            </a>
            <a href="login.php" class="btn btn-outline btn-lg" style="border-color: rgba(255,255,255,0.5); color: white;">
                Déjà inscrit ? Se connecter
            </a>
        </div>
    </div>
</section>

<!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <a href="#" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <span class="logo-text">Solidarité<span>Connect</span></span>
                </a>
                <p class="footer-about">
                    La plateforme qui connecte la générosité des citoyens avec les besoins réels des associations locales pour un impact social concret.
                </p>
                <div class="footer-socials">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="footer-nav">
                <h4 class="footer-heading">Navigation</h4>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Accueil</a></li>
                    <li><a href="#besoins"><i class="fas fa-chevron-right"></i> Besoins</a></li>
                    <li><a href="#associations"><i class="fas fa-chevron-right"></i> Associations</a></li>
                    <li><a href="#comment-ca-marche"><i class="fas fa-chevron-right"></i> Comment ça marche</a></li>
                </ul>
            </div>

            <div class="footer-nav">
                <h4 class="footer-heading">Support</h4>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Aide & FAQ</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Contactez-nous</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Mentions légales</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Confidentialité</a></li>
                </ul>
            </div>

            <div class="footer-contact">
                <h4 class="footer-heading">Contact</h4>
                <div class="footer-contact-item">
                    <div class="footer-contact-icon">
                        <i class="fas fa-location-dot"></i>
                    </div>
                    <div class="footer-contact-text">
                        <strong>Adresse</strong>
                        123 Rue de la Solidarité, Casablanca
                    </div>
                </div>
                <div class="footer-contact-item">
                    <div class="footer-contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="footer-contact-text">
                        <strong>Téléphone</strong>
                        +212 5 22 00 00 00
                    </div>
                </div>
                <div class="footer-contact-item">
                    <div class="footer-contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="footer-contact-text">
                        <strong>Email</strong>
                        contact@solidariteconnect.ma
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-copyright">
                &copy; 2026 Solidarité Connect. 
            </div>
            <div class="footer-bottom-links">
                <a href="#">Conditions d'utilisation</a>
                <a href="#">Politique de cookies</a>
            </div>
        </div>
    </footer>

<script>
// Navbar scroll effect
window.addEventListener('scroll', () => {
    const navbar = document.getElementById('navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});
</script>
</body>
</html>

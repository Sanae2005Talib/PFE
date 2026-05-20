<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'association') {
    header("Location: login.php");
    exit();
}

// Get user and association data
$stmt = $pdo->prepare("
    SELECT u.email, u.name as user_name, a.* 
    FROM users u 
    JOIN associations a ON u.id = a.user_id 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$userData = $stmt->fetch();

if (!$userData) {
    die("Utilisateur non trouvé.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Solidarité Connect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: var(--gray-50);
            min-height: 100vh;
        }

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
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .main-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-card {
            background: var(--white);
            border-radius: var(--radius-3xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-100);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .profile-header {
            background: var(--gradient-primary);
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
        }

        .avatar-upload {
            position: relative;
            width: 150px;
            height: 150px;
            margin: -75px auto 1.5rem;
            z-index: 2;
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid var(--white);
            box-shadow: var(--shadow-lg);
            background-size: cover;
            background-position: center;
            background-color: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: var(--primary-500);
            overflow: hidden;
        }

        .avatar-edit {
            position: absolute;
            right: 5px;
            bottom: 5px;
            z-index: 3;
        }

        .avatar-edit input {
            display: none;
        }

        .avatar-edit label {
            width: 40px;
            height: 40px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-md);
            color: var(--primary-600);
            transition: all 0.3s ease;
        }

        .avatar-edit label:hover {
            transform: scale(1.1);
            background: var(--primary-50);
        }

        .profile-body {
            padding: 2rem 3rem 4rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--gray-800);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-50);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .btn-save {
            width: 100%;
            margin-top: 2rem;
            padding: 1.25rem;
            background: var(--gradient-primary);
            color: var(--white);
            border: none;
            border-radius: var(--radius-xl);
            font-size: 1.125rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
            .profile-body {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="dash-navbar">
        <div class="dash-navbar-inner">
            <a href="association_dashboard.php" class="logo">
                <div class="logo-icon"><i class="fas fa-arrow-left"></i></div>
                <div class="logo-text">Retour<span></span></div>
            </a>
            <div class="logo-text" style="font-size: 1.25rem;">Mon Profil</div>
            <div></div>
        </div>
    </nav>

    <div class="main-container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Profil mis à jour avec succès !
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                    if($_GET['error'] == 'upload') echo "Erreur lors du téléchargement de l'image.";
                    elseif($_GET['error'] == 'type') echo "Type de fichier non autorisé (JPG, PNG uniquement).";
                    else echo "Une erreur est survenue.";
                ?>
            </div>
        <?php endif; ?>

        <form action="traitement_profil.php" method="POST" enctype="multipart/form-data" class="profile-card">
            <div class="profile-header"></div>
            
            <div class="avatar-upload">
                <div class="avatar-preview" id="imagePreview">
                    <?php if ($userData['logo']): ?>
                        <img src="uploads/logos/<?= $userData['logo'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-building"></i>
                    <?php endif; ?>
                </div>
                <div class="avatar-edit">
                    <input type='file' name="logo" id="imageUpload" accept=".png, .jpg, .jpeg" />
                    <label for="imageUpload"><i class="fas fa-camera"></i></label>
                </div>
            </div>

            <div class="profile-body">
                <h2 class="section-title"><i class="fas fa-info-circle"></i> Informations Générales</h2>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nom de l'association</label>
                        <input type="text" name="association_name" class="form-input" value="<?= htmlspecialchars($userData['association_name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nom du responsable</label>
                        <input type="text" name="user_name" class="form-input" value="<?= htmlspecialchars($userData['user_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Professionnel</label>
                        <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($userData['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" name="phone" class="form-input" value="<?= htmlspecialchars($userData['phone']) ?>" required>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Adresse Siège</label>
                        <input type="text" name="address" class="form-input" value="<?= htmlspecialchars($userData['address']) ?>" required>
                    </div>
                </div>

                <h2 class="section-title" style="margin-top: 3rem;"><i class="fas fa-lock"></i> Sécurité</h2>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="password" name="new_password" class="form-input" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('imageUpload').onchange = function (evt) {
            const [file] = this.files
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>

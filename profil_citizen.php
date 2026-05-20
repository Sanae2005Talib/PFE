<?php
session_start();
include_once 'api/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: login.php");
    exit();
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur non trouvé.");
}

// Check if user has profile photo
$profile_photo = null;
if (!empty($user['profile_photo'])) {
    $profile_photo = $user['profile_photo'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Solidarité Connect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: #f8fafc; 
            min-height: 100vh;
        }

        /* Header */
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            z-index: 100;
        }
        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 800;
            color: #10b981;
            text-decoration: none;
        }
        .header-logo i { font-size: 1.5rem; }
        .back-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #f1f5f9;
            color: #64748b;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .back-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        /* Main */
        .main-content {
            margin-top: 70px;
            padding: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Alert */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
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

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, #10b981, #059669, #0ea5e9);
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
        }
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(ellipse at 20% 30%, rgba(255,255,255,0.15) 0%, transparent 50%);
        }

        .avatar {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            color: #10b981;
            font-weight: 800;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-upload-wrapper {
            position: relative;
            display: inline-block;
        }

        .avatar-edit-btn {
            position: absolute;
            bottom: 0;
            right: -10px;
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            color: #10b981;
            transition: all 0.3s;
            z-index: 2;
        }

        .avatar-edit-btn:hover {
            transform: scale(1.1);
            background: #10b981;
            color: white;
        }

        .avatar-edit-btn input {
            display: none;
        }

        .profile-name {
            font-size: 1.75rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .profile-role {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .profile-body {
            padding: 2.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }
        .section-title i { color: #10b981; }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            color: #1e293b;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .btn-save {
            width: 100%;
            margin-top: 2rem;
            padding: 1.25rem;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.125rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        }

        @media (max-width: 768px) {
            .top-header { padding: 0 1rem; }
            .main-content { padding: 1rem; }
            .profile-body { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="top-header">
        <a href="donateur_dashboard.php" class="header-logo">
            <i class="fas fa-hand-holding-heart"></i>
            <span>SolidaritéConnect</span>
        </a>
        <a href="donateur_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Retour
        </a>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Profil mis à jour avec succès !
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                    if($_GET['error'] == 'password') echo "Les mots de passe ne correspondent pas.";
                    elseif($_GET['error'] == 'email') echo "Cet email est déjà utilisé.";
                    elseif($_GET['error'] == 'upload') echo "Erreur lors du téléchargement de l'image.";
                    elseif($_GET['error'] == 'type') echo "Type de fichier non autorisé (JPG, PNG uniquement).";
                    else echo "Une erreur est survenue.";
                ?>
            </div>
        <?php endif; ?>

        <form action="traitement_profil_citizen.php" method="POST" enctype="multipart/form-data" class="profile-card">
            <div class="profile-header">
                <div class="avatar-upload-wrapper">
                    <div class="avatar" id="avatarPreview">
                        <?php if ($profile_photo): ?>
                            <img src="uploads/profiles/<?= htmlspecialchars($profile_photo) ?>" alt="Profile">
                        <?php else: ?>
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <label for="profilePhoto" class="avatar-edit-btn">
                        <i class="fas fa-camera"></i>
                        <input type="file" id="profilePhoto" name="profile_photo" accept="image/jpeg,image/png,image/jpg">
                    </label>
                </div>
                <h1 class="profile-name"><?= htmlspecialchars($user['name']) ?></h1>
                <p class="profile-role">Donateur</p>
            </div>

            <div class="profile-body">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    Informations Personnelles
                </h2>
                
                <div class="form-group">
                    <label class="form-label">Nom Complet</label>
                    <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Adresse Email</label>
                    <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <h2 class="section-title" style="margin-top: 2rem;">
                    <i class="fas fa-lock"></i>
                    Changer le Mot de Passe
                </h2>

                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" name="new_password" class="form-input" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="confirm_password" class="form-input" placeholder="••••••••">
                </div>

                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </main>

    <script>
        // Preview image before upload
        document.getElementById('profilePhoto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('avatarPreview').innerHTML = 
                        `<img src="${event.target.result}" alt="Profile">`;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>

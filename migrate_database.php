<?php
/**
 * Script de migration de la base de données
 * Ajoute les nouvelles colonnes à la table 'needs'
 * 
 * IMPORTANT : Exécutez ce fichier UNE SEULE FOIS
 * Accédez à : http://votre-site.com/migrate_database.php
 */

include_once 'api/conn.php';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Migration Base de Données</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #10b981;
            border-bottom: 3px solid #10b981;
            padding-bottom: 10px;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #10b981;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #ef4444;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #3b82f6;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 Migration de la Base de Données</h1>";

try {
    // Vérifier si les colonnes existent déjà
    $check_needs = $pdo->query("SHOW COLUMNS FROM needs LIKE 'quantity'")->fetch();
    $check_users = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_photo'")->fetch();
    
    if ($check_needs && $check_users) {
        echo "<div class='info'>
                <strong>ℹ️ Information :</strong> Les colonnes existent déjà. Migration déjà effectuée.
              </div>";
    } else {
        echo "<div class='info'>
                <strong>🔄 Début de la migration...</strong>
              </div>";
        
        // Migration table needs
        if (!$check_needs) {
            // Ajouter la colonne quantity
            $pdo->exec("ALTER TABLE needs ADD COLUMN quantity VARCHAR(255) DEFAULT NULL");
            echo "<div class='success'>✅ Colonne <code>quantity</code> ajoutée à la table needs</div>";
            
            // Ajouter la colonne location
            $pdo->exec("ALTER TABLE needs ADD COLUMN location VARCHAR(255) DEFAULT NULL");
            echo "<div class='success'>✅ Colonne <code>location</code> ajoutée à la table needs</div>";
            
            // Ajouter la colonne deadline
            $pdo->exec("ALTER TABLE needs ADD COLUMN deadline DATE DEFAULT NULL");
            echo "<div class='success'>✅ Colonne <code>deadline</code> ajoutée à la table needs</div>";
        }
        
        // Migration table users
        if (!$check_users) {
            // Ajouter la colonne profile_photo
            $pdo->exec("ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL");
            echo "<div class='success'>✅ Colonne <code>profile_photo</code> ajoutée à la table users</div>";
        }
        
        echo "<div class='success'>
                <strong>🎉 Migration terminée avec succès !</strong><br>
                Vous pouvez maintenant utiliser les nouvelles fonctionnalités.
              </div>";
    }
    
    // Afficher la structure de la table needs
    echo "<h2>📋 Structure actuelle de la table 'needs'</h2>";
    $columns = $pdo->query("SHOW COLUMNS FROM needs")->fetchAll();
    
    echo "<table style='width:100%; border-collapse: collapse; margin-top: 15px;'>
            <thead>
                <tr style='background: #f3f4f6;'>
                    <th style='padding: 10px; text-align: left; border: 1px solid #e5e7eb;'>Colonne</th>
                    <th style='padding: 10px; text-align: left; border: 1px solid #e5e7eb;'>Type</th>
                    <th style='padding: 10px; text-align: left; border: 1px solid #e5e7eb;'>Null</th>
                    <th style='padding: 10px; text-align: left; border: 1px solid #e5e7eb;'>Défaut</th>
                </tr>
            </thead>
            <tbody>";
    
    foreach ($columns as $col) {
        $isNew = in_array($col['Field'], ['quantity', 'location', 'deadline']);
        $style = $isNew ? "background: #d1fae5;" : "";
        
        echo "<tr style='$style'>
                <td style='padding: 10px; border: 1px solid #e5e7eb;'><strong>{$col['Field']}</strong></td>
                <td style='padding: 10px; border: 1px solid #e5e7eb;'>{$col['Type']}</td>
                <td style='padding: 10px; border: 1px solid #e5e7eb;'>{$col['Null']}</td>
                <td style='padding: 10px; border: 1px solid #e5e7eb;'>" . ($col['Default'] ?? 'NULL') . "</td>
              </tr>";
    }
    
    echo "</tbody></table>";
    
    // Afficher la structure de la table users
    echo "<h2>📋 Structure actuelle de la table 'users'</h2>";
    $columns_users = $pdo->query("SHOW COLUMNS FROM users")->fetchAll();
    
    echo "<table style='width:100%; border-collapse: collapse; margin-top: 15px;'>
            <thead>
                <tr style='background: #f3f4f6;'>
                    <th style='padding: 10px; text-align: left; border: 1px solid #e5e7eb;'>Colonne</th>
                    <th style='padding: 10px; text-align: left; border: 1px solid #e5e7eb;'>Type</th>
                    <th style='padding: 10px; text-align: left; border: 1px solid #e5e7eb;'>Null</th>
                    <th style='padding: 10px; text-align: left; border: 1px solid #e5e7eb;'>Défaut</th>
                </tr>
            </thead>
            <tbody>";
    
    foreach ($columns_users as $col) {
        $isNew = in_array($col['Field'], ['profile_photo']);
        $style = $isNew ? "background: #d1fae5;" : "";
        
        echo "<tr style='$style'>
                <td style='padding: 10px; border: 1px solid #e5e7eb;'><strong>{$col['Field']}</strong></td>
                <td style='padding: 10px; border: 1px solid #e5e7eb;'>{$col['Type']}</td>
                <td style='padding: 10px; border: 1px solid #e5e7eb;'>{$col['Null']}</td>
                <td style='padding: 10px; border: 1px solid #e5e7eb;'>" . ($col['Default'] ?? 'NULL') . "</td>
              </tr>";
    }
    
    echo "</tbody></table>";
    
    echo "<div class='info' style='margin-top: 30px;'>
            <strong>⚠️ Important :</strong> Pour des raisons de sécurité, supprimez ce fichier après la migration :<br>
            <code>migrate_database.php</code>
          </div>";
    
    echo "<div style='margin-top: 20px; text-align: center;'>
            <a href='association_dashboard.php' style='display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                Retour au Dashboard
            </a>
          </div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>
            <strong>❌ Erreur :</strong> " . htmlspecialchars($e->getMessage()) . "
          </div>";
    
    echo "<div class='info'>
            <strong>💡 Solution :</strong> Vérifiez que :
            <ul>
                <li>La connexion à la base de données est correcte</li>
                <li>L'utilisateur a les permissions ALTER TABLE</li>
                <li>La table 'needs' existe bien</li>
            </ul>
          </div>";
}

echo "    </div>
</body>
</html>";
?>

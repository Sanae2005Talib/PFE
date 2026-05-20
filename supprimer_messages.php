<?php
/**
 * Script pour supprimer la table messages
 * Accédez à : http://localhost/app.solidarite/supprimer_messages.php
 */

include_once 'api/conn.php';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Suppression Table Messages</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #ef4444; }
        .success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #dbeafe; color: #1e40af; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fef3c7; color: #92400e; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🗑️ Suppression Table Messages</h1>";

try {
    // Vérifier si la table existe
    $check = $pdo->query("SHOW TABLES LIKE 'messages'")->fetch();
    
    if (!$check) {
        echo "<div class='info'><strong>ℹ️ Information :</strong> La table 'messages' n'existe pas.</div>";
    } else {
        echo "<div class='warning'><strong>⚠️ Attention :</strong> Suppression de la table 'messages' en cours...</div>";
        
        // Supprimer la table
        $pdo->exec("DROP TABLE IF EXISTS messages");
        
        echo "<div class='success'><strong>✅ Succès !</strong> La table 'messages' a été supprimée avec succès.</div>";
    }
    
    // Afficher toutes les tables restantes
    echo "<h2>📋 Tables restantes dans la base de données</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    
    if (empty($tables)) {
        echo "<p>Aucune table trouvée.</p>";
    } else {
        echo "<ul style='list-style: none; padding: 0;'>";
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            echo "<li style='padding: 8px; background: #f3f4f6; margin: 5px 0; border-radius: 5px;'>
                    <i class='fas fa-table'></i> <strong>$tableName</strong>
                  </li>";
        }
        echo "</ul>";
    }
    
    echo "<div class='success' style='margin-top: 30px;'>
            <strong>🎉 Opération terminée !</strong><br>
            La table messages a été supprimée de votre base de données.
          </div>";
    
    echo "<div style='margin-top: 20px; text-align: center;'>
            <a href='index.php' style='display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                Retour à l'accueil
            </a>
          </div>";
    
    echo "<div class='info' style='margin-top: 30px;'>
            <strong>⚠️ Important :</strong> Supprimez ce fichier après utilisation pour des raisons de sécurité.
          </div>";
    
} catch (PDOException $e) {
    echo "<div class='error'><strong>❌ Erreur :</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "    </div>
</body>
</html>";
?>

<?php
// Script pour vérifier les comptes admin dans la base de données
require_once 'api/conn.php';

echo "<h2>🔍 Vérification des comptes Admin</h2>";

// Récupérer tous les comptes admin
$query = "SELECT id, name, email, role, created_at FROM users WHERE role = 'admin' ORDER BY id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo "<h3>✅ Comptes Admin trouvés:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #10B981; color: white;'>";
    echo "<th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Date création</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td><strong>" . $row['email'] . "</strong></td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<br><h3>📝 Comptes disponibles pour login:</h3>";
    mysqli_data_seek($result, 0); // Reset pointer
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div style='background-color: #f0f0f0; padding: 10px; margin: 10px 0; border-left: 4px solid #10B981;'>";
        echo "<strong>Email:</strong> " . $row['email'] . "<br>";
        echo "<strong>Password:</strong> password123 (si créé avec create_test_accounts.sql)<br>";
        echo "<strong>Nom:</strong> " . $row['name'];
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>❌ Aucun compte admin trouvé!</p>";
    echo "<p>Exécutez le fichier <strong>create_test_accounts.sql</strong> dans phpMyAdmin pour créer les comptes de test.</p>";
}

echo "<br><hr><br>";

// Vérifier aussi les autres rôles
echo "<h3>📊 Tous les utilisateurs par rôle:</h3>";
$query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$result = mysqli_query($conn, $query);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background-color: #0EA5E9; color: white;'>";
echo "<th>Rôle</th><th>Nombre</th>";
echo "</tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['role'] . "</td>";
    echo "<td>" . $row['count'] . "</td>";
    echo "</tr>";
}

echo "</table>";

mysqli_close($conn);
?>

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background-color: #f5f5f5;
    }
    h2 {
        color: #10B981;
    }
    h3 {
        color: #0EA5E9;
    }
</style>

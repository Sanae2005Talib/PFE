<?php
// Script pour afficher TOUS les users dans la database
$host = 'localhost';
$dbname = 'solidarite_connect';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Erreur connexion: " . mysqli_connect_error());
}

echo "<h2>👥 Tous les utilisateurs dans la base de données</h2>";

// Récupérer TOUS les users
$query = "SELECT id, name, email, role FROM users ORDER BY role, id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #10B981; color: white;'>";
    echo "<th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Action</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $bgColor = '';
        if ($row['role'] == 'admin') $bgColor = 'background-color: #ffe6e6;';
        if ($row['role'] == 'association') $bgColor = 'background-color: #e6f3ff;';
        if ($row['role'] == 'citizen') $bgColor = 'background-color: #f0f0f0;';
        
        echo "<tr style='$bgColor'>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td><strong>" . $row['email'] . "</strong></td>";
        echo "<td><span style='padding: 5px 10px; background: #10B981; color: white; border-radius: 5px;'>" . $row['role'] . "</span></td>";
        echo "<td><button onclick='testLogin(\"" . $row['email'] . "\")' style='padding: 5px 10px; background: #0EA5E9; color: white; border: none; border-radius: 5px; cursor: pointer;'>Test Login</button></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<br><h3>📊 Statistiques:</h3>";
    $stats_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
    $stats_result = mysqli_query($conn, $stats_query);
    
    echo "<ul>";
    while ($stat = mysqli_fetch_assoc($stats_result)) {
        echo "<li><strong>" . $stat['role'] . ":</strong> " . $stat['count'] . " utilisateur(s)</li>";
    }
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'>❌ Aucun utilisateur trouvé dans la base de données!</p>";
}

mysqli_close($conn);
?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background-color: #f5f5f5;
}
h2, h3 {
    color: #10B981;
}
table {
    background: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
</style>

<script>
function testLogin(email) {
    const password = prompt('Entrez le mot de passe pour ' + email + ':', 'password123');
    if (password) {
        window.open('test_login_api.php?email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password), '_blank');
    }
}
</script>

<br><br>
<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
    <h3>💡 Instructions:</h3>
    <ol>
        <li>Regardez les emails disponibles ci-dessus</li>
        <li>Cliquez sur "Test Login" pour tester un compte</li>
        <li>Ou utilisez directement dans Flutter avec le mot de passe correct</li>
    </ol>
    <p><strong>Note:</strong> Si vous ne connaissez pas le mot de passe, je peux le réinitialiser pour vous.</p>
</div>

<br>
<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; border-left: 4px solid #0c5460;'>
    <h3>🔧 Besoin de réinitialiser un mot de passe?</h3>
    <p>Dites-moi quel email vous voulez utiliser et je vais mettre le mot de passe à "password123"</p>
</div>

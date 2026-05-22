<?php
// 1. Njîbo l-ma3loumat dyal la base de données automatiquement
$host     = getenv('MYSQLHOST') ?: 'localhost';
$port     = getenv('MYSQLPORT') ?: '3306';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: ''; // Khawi f Laragon
$dbname   = getenv('MYSQLDATABASE') ?: 'solidarite_connect'; 

// 2. PDO Connection (le code l-qdim dyalk)
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur PDO: " . $e->getMessage());
}

// 3. MySQLi Connection (le code l-jdid dyalk)
$conn = mysqli_connect($host, $username, $password, $dbname, $port);

if (!$conn) {
    die("Erreur MySQLi: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>
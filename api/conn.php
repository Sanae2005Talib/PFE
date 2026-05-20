<?php
$host = 'localhost';
$dbname = 'solidarite_connect';
$username = 'root'; 
$password = ''; 

// PDO Connection (pour compatibilité avec ancien code)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur PDO: " . $e->getMessage());
}

// MySQLi Connection (pour nouveau code)
$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Erreur MySQLi: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>
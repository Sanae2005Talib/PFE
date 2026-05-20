<?php
include_once 'api/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $assoc_name = $_POST['assoc_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address']; // Zdna hada kima 3ndk f l-base
    $region_id = $_POST['region_id'];

    try {
        $pdo->beginTransaction();

        // 1. Zid l-User kima rsemti f l-base jdid
        $stmt1 = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'association')");
        $stmt1->execute([$user_name, $email, $password]);
        
        $user_id = $pdo->lastInsertId();

        // 2. Zid l-Association m3a region_id dyalha jdid
        $stmt2 = $pdo->prepare("INSERT INTO associations (user_id, association_name, phone, address, region_id, is_validated) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt2->execute([$user_id, $assoc_name, $phone, $address, $region_id]);

        $pdo->commit();
        header("Location: login.php?success=registered");

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erreur d'inscription : " . $e->getMessage();
    }
}
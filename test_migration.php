<?php
/**
 * Script de test pour vérifier la migration
 * Vérifie que toutes les colonnes sont présentes
 */

include_once 'api/conn.php';

header('Content-Type: application/json');

try {
    // Vérifier les colonnes
    $columns = $pdo->query("SHOW COLUMNS FROM needs")->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredColumns = ['quantity', 'location', 'deadline'];
    $existingColumns = array_column($columns, 'Field');
    
    $missingColumns = array_diff($requiredColumns, $existingColumns);
    
    $response = [
        'success' => empty($missingColumns),
        'message' => empty($missingColumns) 
            ? 'Toutes les colonnes sont présentes ✅' 
            : 'Colonnes manquantes : ' . implode(', ', $missingColumns),
        'columns' => $columns,
        'required' => $requiredColumns,
        'existing' => $existingColumns,
        'missing' => array_values($missingColumns)
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>

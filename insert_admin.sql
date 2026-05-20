-- Créer compte admin pour test
-- Exécuter ce script dans phpMyAdmin

USE solidarite_connect;

-- Supprimer ancien compte admin@test.com si existe
DELETE FROM users WHERE email = 'admin@test.com';

-- Créer nouveau compte admin
-- Email: admin@test.com
-- Password: password123
INSERT INTO users (name, email, password, role) 
VALUES (
    'Admin Test', 
    'admin@test.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'admin'
);

-- Vérifier que le compte est créé
SELECT id, name, email, role FROM users WHERE email = 'admin@test.com';

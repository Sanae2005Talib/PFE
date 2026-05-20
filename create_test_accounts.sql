-- Test Accounts for Solidarité Connect
-- Run this in phpMyAdmin or MySQL

USE solidarite_connect;

-- Delete old test accounts if exist
DELETE FROM users WHERE email IN ('citizen@test.com', 'association@test.com', 'admin@test.com');

-- Test Citizen Account
-- Email: citizen@test.com
-- Password: password123
INSERT INTO users (name, email, password, role) 
VALUES ('Test Citizen', 'citizen@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'citizen');

-- Test Association Account
-- Email: association@test.com
-- Password: password123
INSERT INTO users (name, email, password, role) 
VALUES ('Manager Association', 'association@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'association');

-- Create association details (get last inserted user_id)
INSERT INTO associations (user_id, association_name, phone, address, region_id, is_validated, description) 
SELECT id, 'Association Test', '+212 600000000', 'Casablanca, Morocco', 1, 1, 'Association de test'
FROM users WHERE email = 'association@test.com';

-- Test Admin Account
-- Email: admin@test.com
-- Password: password123
INSERT INTO users (name, email, password, role) 
VALUES ('Admin Test', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

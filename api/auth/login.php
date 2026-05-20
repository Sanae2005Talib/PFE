<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../conn.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validation
if (!isset($data->email) || !isset($data->password)) {
    echo json_encode([
        "success" => false,
        "message" => "Email et mot de passe requis"
    ]);
    exit();
}

$email = mysqli_real_escape_string($conn, $data->email);

// Query user
$query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur de base de données: " . mysqli_error($conn)
    ]);
    exit();
}

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    
    // Verify password
    if (password_verify($data->password, $user['password'])) {
        // Password correct - Login success
        $userData = [
            "id" => (int)$user['id'],
            "name" => $user['name'],
            "email" => $user['email'],
            "role" => $user['role']
        ];
        
        // If association, get association details
        if ($user['role'] === 'association') {
            $assoc_query = "
                SELECT a.*, r.name as region_name 
                FROM associations a 
                LEFT JOIN regions r ON a.region_id = r.id 
                WHERE a.user_id = " . $user['id'];
            $assoc_result = mysqli_query($conn, $assoc_query);
            
            if ($assoc_result && mysqli_num_rows($assoc_result) > 0) {
                $userData['association'] = mysqli_fetch_assoc($assoc_result);
            }
        }
        
        // Generate simple token (timestamp + user_id)
        $token = base64_encode($user['id'] . ':' . time());
        
        echo json_encode([
            "success" => true,
            "message" => "Connexion réussie",
            "user" => $userData,
            "token" => $token
        ]);
    } else {
        // Password incorrect
        echo json_encode([
            "success" => false,
            "message" => "Email ou mot de passe incorrect"
        ]);
    }
} else {
    // User not found
    echo json_encode([
        "success" => false,
        "message" => "Email ou mot de passe incorrect"
    ]);
}

mysqli_close($conn);
?>

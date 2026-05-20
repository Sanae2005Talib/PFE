<?php
// Had l-headers daroryin bach Flutter may tbloquach (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Hadchi li radi ychouf Flutter
$reponse = [
    "status" => "success",
    "message" => "Daba l-connexion khdama mzyan!"
];

echo json_encode($reponse);
?>
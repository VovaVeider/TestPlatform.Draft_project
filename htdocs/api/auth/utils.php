<?php
require_once  '..\vendor\autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
function generate_jwt(string $id,string $login,string $role){
    require  'secret.php';
    $payload = [
        'id' => $id,
        'login' => $login,
        'role' => $role,

    ];
    return $jwt = JWT::encode($payload, $secret_code, 'HS256');
}




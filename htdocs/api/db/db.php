<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
function create_conn(): PDO
{
    require 'db_auth.php';
    try {
        return new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name;user=$db_user;password=$db_password");
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        $answer['error'] = 'SERVER_ERR';
        $answer['error_descr'] = $api_errors['SERVER_ERR'];
        echo json_encode($answer);
        exit;
    }
}
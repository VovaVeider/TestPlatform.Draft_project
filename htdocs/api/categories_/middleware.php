<?php
require_once '../db/db.php';

function get_catid_url():int|false
{
    $uri = $_SERVER['REQUEST_URI'];
    if (!isset($uri)){
        return false;
    }
    $path = parse_url($uri,PHP_URL_PATH);
    if (preg_match('#^/categories/\d+(($)|(/.*$))#',$path)){
        $elems = explode('/',$uri);
        return (int)$elems[2];
    } else{
        return false;
    }

}
function check_get_catid():int{
    $id = get_catid_url();
    require  '../api_errors/errors.php';
    if ($id === false){
        //Такого быть не должно
    }
    else{
        $conn = create_conn();
        $stmt=$conn->prepare('SELECT COUNT (*) AS NUM FROM categories WHERE ID =:id');
        $stmt->execute(['id'=>$id]);
        $result = $stmt->fetch();
        if ($result['num'] == 0 ){
            $answer = ['error' => 'СAT_NOTFND', 'error_descr' => $api_errors['СAT_NOTFND']];
            http_response_code(404);
            echo json_encode($answer);
            exit;
        } else {
            return $id;
        }

    }
}

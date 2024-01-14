<?php
require_once 'utils.php';
require_once '../db/db.php';
function check_get_test():int{
    $id = get_testid_url();
    require  '../api_errors/errors.php';
    if ($id === false){
        //Такого быть не должно
    }
    else{
        $conn = create_conn();
        $stmt=$conn->prepare('SELECT COUNT (*) AS NUM FROM TESTS WHERE ID =:id');
        $stmt->execute(['id'=>$id]);
        $result = $stmt->fetch();
        if ($result['num'] == 0 ){
            header('Content-Type: application/json');
            $answer = ['error' => 'TEST_NOT_FOUND', 'error_descr' => $api_errors['TEST_NOT_FOUND']];
            http_response_code(404);
            echo json_encode($answer);
            exit;
        } else {
            return $id;
        }

    }
}
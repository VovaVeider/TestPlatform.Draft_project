<?php
require_once  '../db/db.php';
header('Content-Type: application/json');
$answer['error'] = null;
$answer['error_descr'] = null;
$conn = create_conn();
$stmt=$conn->prepare('SELECT * FROM categories');
$stmt->execute();
$answer['categories'] = [];
while($category =$stmt->fetch()){
    $answer['categories'][] = ['id'=>$category['id'],'name'=>$category['name']];
}
echo json_encode($answer,JSON_UNESCAPED_UNICODE);

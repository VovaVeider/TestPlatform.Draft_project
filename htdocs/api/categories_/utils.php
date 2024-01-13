<?php
require_once '../db/db.php';
function category_name_is_occupied(string $name ):bool{
    $conn = create_conn();
    $stmt=$conn->prepare('SELECT COUNT (*) AS NUM FROM categories WHERE name =:name');
    $stmt->execute(['name'=>$name]);
    $result = $stmt->fetch();
    if ($result['num'] != 0 ){
       return true;
    } else {
        return false;
    }
}
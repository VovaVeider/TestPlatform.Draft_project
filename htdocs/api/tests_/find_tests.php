<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
require_once '../auth/middleware.php';
require_once '../db/db.php';
header('Content-Type: application/json');
$payload = rights_auth_check(['admin', 'user', 'guest']);

$role = $payload['role'];
$user_id = $payload['id'];
$page_num = (isset($_GET['page']) && (int)$_GET['page'] > 0) ? (int)$_GET['page'] : 1;
$elem_amount = (isset($_GET['amount']) && (int)$_GET['amount'] > 0) ? (int)$_GET['amount'] : 10;
$sort = (isset($_GET['sort']) &&  $_GET['sort'] === 'DESC') ? $_GET['sort'] : 'ASC';
$categories = (isset($_GET['categories'])) ? json_decode($_GET['categories'], true) : null;
$favorite = (isset($_GET['favorite']) && ($_GET['favorite']) === 'true') ? true : false;
$starts_with = (isset($_GET['starts-with']) && trim((string)$_GET['starts-with'] !== '')) ? (string)$_GET['starts-with'] : '';


if ($categories === null) {
    $cat_sql = '';
} else {
    $cat_sql = str_replace('-1','null','and category_id  in(' . implode(',',
             array_filter($categories, function ($value) {
                return $value !== -1;
            })).')');
    if (count($categories)===0){
        $cat_sql ='';
    }
    if (in_array(-1,$categories)){
        $cat_sql= ' and category_id IS NULL';
    }
}
$offset = ($page_num-1) * $elem_amount;
if ($favorite === true && $role != 'guest') {
    $sql_cnt = " SELECT count(*)  as cnt
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id JOIN favorites on 
              favorites.test_id = tests.id and favorites.user_id = $user_id
          WHERE tests.name LIKE '$starts_with%'  $cat_sql";

    $sql = " SELECT  *, true as favorite
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id JOIN favorites on 
              favorites.test_id = tests.id and favorites.user_id = $user_id
          WHERE tests.name LIKE '$starts_with%'  $cat_sql
          ORDER BY passed $sort
          OFFSET $offset
          LIMIT $elem_amount";
} elseif ($role === 'guest') {
    $sql_cnt = " SELECT count(*)  as cnt
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id
          WHERE tests.name LIKE '$starts_with%'  $cat_sql";

    $sql = " SELECT  *, false as favorite
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id 
          WHERE tests.name LIKE '$starts_with%'  $cat_sql
          ORDER BY passed $sort
          OFFSET $offset
          LIMIT $elem_amount";
} else {
    $sql_cnt = " SELECT count(*) as cnt
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id 
          WHERE tests.name LIKE '$starts_with%'  $cat_sql";

    $sql = " SELECT  *, CASE 
           WHEN tests.id IN (SELECT test_id FROM favorites WHERE user_id = $user_id) THEN true
           ELSE false
            END AS favorite
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id 
          WHERE tests.name LIKE '$starts_with%'  $cat_sql
          ORDER BY passed $sort
          OFFSET $offset
          LIMIT $elem_amount";
}
$answer = ['error'=>null,'error_desc'=>null];
$answer['tests'] = [];
$conn = create_conn();
$pages = (int)ceil((float)$conn->query($sql_cnt)->fetch()['cnt'] / $elem_amount);
$answer['pages'] = $pages;
$stmt=$conn->query($sql);
while ($test = $stmt->fetch()) {
    $answer['tests'][] = [
        'id' => $test[2],
        'name' => $test[3],
        'description' => $test[4],
        'category_id' => ($test[5]===null)?-1:$test[5],
        'favorite' => (bool)$test[9],
        'preview' => ($test[8] === null) ? null :
            'api.testplatform.ru/tests/' . strval($test['2']) . '/' . 'preview',
        'category_name' => $test['category.name'],
        'passed' => $test[7]];
}
echo json_encode($answer,JSON_UNESCAPED_UNICODE);


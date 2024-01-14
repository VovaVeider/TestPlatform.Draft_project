<?php
require_once '../auth/middleware.php';
require_once '../db/db.php';
header('Content-Type: application/json');
$payload = rights_auth_check(['admin', 'user', 'guest']);

$role = $payload['role'];
$user_id = $payload['id'];
$page_num = (isset($_GET['page']) && (int)$_GET['page'] > 0) ? (int)$_GET['page'] : 1;
$elem_amount = (isset($_GET['amount']) && (int)$_GET['amount'] > 0) ? (int)$_GET['amount'] : 10;
$sort = (isset($_GET['sort']) && ($_GET['sort']) === 'asc' || $_GET['sort'] === 'desc') ? $_GET['sort'] : 'asc';
$categories = (isset($_GET['categories'])) ? json_decode($_GET['categories'], true) : 'null';
$favorite = (isset($_GET['favorite']) && ($_GET['favorite']) === 'true') ? true : false;
$starts_with = (isset($_GET['starts-with']) && trim((string)$_GET['starts-with'] !== '')) ? (string)$_GET['starts-with'] : '';

$categories = array_map(fn($value) => ($value === -1) ? null : $value, $categories);
if ($categories === null) {
    $cat_sql = '';
} else {
    $cat_sql = 'and category_id  in(' . implode(',', $categories) . ')';
}
$offset = $page_num * $elem_amount;
if ($favorite === true && $role != 'guest') {
    $sql_cnt = " SELECT count(*)  as cnt
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id JOIN favorites on 
              favorites.test_id = tests.id and favorites.user_id = $user_id
          WHERE tests.name LIKE '$starts_with%'  $cat_sql
          ORDER BY passed $sort";

    $sql = " SELECT  *, 'true' as favorite
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id JOIN favorites on 
              favorites.test_id = tests.id and favorites.user_id = $user_id
          WHERE tests.name LIKE '$starts_with%'  $cat_sql
          ORDER BY passed $sort
          OFFSET $offset
          LIMIT $elem_amount";
} elseif ($role === 'guest') {
    $sql_cnt = " SELECT count(*)  as cnt
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id
          WHERE tests.name LIKE '$starts_with%'  $cat_sql
          ORDER BY passed $sort";

    $sql = " SELECT  *, 'false' as favorite
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id 
          WHERE tests.name LIKE '$starts_with%'  $cat_sql
          ORDER BY passed $sort
          OFFSET $offset
          LIMIT $elem_amount";
} else {
    $sql_cnt = " SELECT count(*) as cnt
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id 
          WHERE tests.name LIKE '$starts_with%'  $cat_sql
          ORDER BY passed $sort";

    $sql = " SELECT  *, CASE 
           WHEN tests.id IN (SELECT test_id FROM favorites WHERE user_id = $user_id) THEN 'true'
           ELSE 'false'
            END AS favorite
          FROM categories RIGHT JOIN tests on categories.id = tests.category_id 
          WHERE tests.name LIKE '$starts_with%'  $cat_sql
          ORDER BY passed $sort
          OFFSET $offset
          LIMIT $elem_amount";
}
$answer = [];
$answer['tests'] = [];
$conn = create_conn();
$pages = ceil((float)$conn->query($sql_cnt)->fetch()['cnt'] / $elem_amount);
$answer['pages'] = $pages;
while ($test = $conn->query($sql)->fetch()) {
    $answer['tests'][] = [
        'id' => $test['tests.id'],
        'name' => $test['tests.name'],
        'description' => $test['tests.description'],
        'category_id' => $test['category_id'],
        'favorite' => (bool)$test['favorite'],
        'preview' => ($test['preview_id'] === null) ? null :
            'api.testplatform.ru/tests/' . strval($test['tests.id']) . '/' . strval($test['preview_id']),
        'category_name' => $test['category.name'],
        'passed' => $test['passed']];
}



<?php
require_once '../auth/middleware.php';
require_once 'middleware.php';
header('Content-Type: application/json');
$payload=rights_auth_check(['admin','user']);
$test_id = check_get_test();
$full = false;
if (isset($_GET['full']) && $_GET['full'] === 'true') {
    $payload = rights_auth_check(['admin']);
    $full = true;
} else {
    $payload = rights_auth_check(['admin', 'user']);
}

$conn = create_conn();
$stmt = $conn->prepare('SELECT * FROM TESTS WHERE ID = :id');
$stmt->execute(['id' => $test_id]);
$test = $stmt->fetch();

$test_id = $test_id;
$name = $test['name'];
$description = $test['description'];
$test_body = json_decode($test['test'], true);
$category = ($test['category_id'] == null) ? -1 : $test['category_id'];
$passed = $test['id'];

if (!$full) {
    unset($test_body['results']);

    foreach ($test_body['questions'] as &$question) {
        foreach ($question['answers'] as &$answer) {
            unset($answer['points']);
        }
    }
    unset($question, $answer);

}

$answer = [
    'error' => null,
    'error_descr' => null,
    'id' => $test_id,
    'name' => $name,
    'description' => $description,
    'category' => $category,
    'test_body' => $test_body];
http_response_code(200);
echo json_encode($answer, JSON_UNESCAPED_UNICODE);
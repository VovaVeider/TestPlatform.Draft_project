<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
require '../vendor/autoload.php'; // Подключаем autoload для Composer
use JsonSchema\Validator;
use JsonSchema\Constraints\Factory;

$test_scheme = file_get_contents('JSON Schemas/Test_JSON_Scheme.json');
function is_valid_test_struct(stdClass $test): bool
{
    //Проверка на соотвествие общей структуре в JSON-схеме
    global $test_scheme;
    $scheme = json_decode($test_scheme, true);
    $validator = new Validator;
    $validator->validate($test, $scheme);
    if (!$validator->isValid()) {
        return false;
    }
    //Если меньше 2-ух вопросов или ни дан не один резуьтат теста
    if (count($test->questions) < 2 || count($test->results) == 0) {
        return false;
    }
    //На каждый вопрос не менее 2-ух ответов
    foreach ($test->questions as $question) {
        if (count($question->answers) < 2) {
            return false;
        }
    }
    if (count($test->results) == 1) {
        return (($test->results[0]->{'min-score'} === $test->results[0]->{'max-score'}) &&
            $test->results[0]->{'max-score'} !== null);
    } else {
        $start_i = 0;
        $end_i = count($test->results) - 1;
        for ($i = $start_i; $i <= $end_i; $i++) {
            $result = $test->results[$i];
            if ($i === $start_i) {
                if ($result->{'min-score'} !== null || $result->{'max-score'} >= $test->results[$i + 1]->{'min-score'}) {
                    return false;
                }
            } elseif ($i === $end_i) {
                if ($result->{'max-score'} !== null) {
                    return false;
                }
            } else {
                if ($result->{'max-score'} >= $test->results[$i + 1]->{'min-score'}) {
                    return false;
                }
            }
        }
        return true;
    }


}

//Проверяем ответ пользователя на соответсвие тесту,т.е не использованы ли варианты
//ответов которых нет в тесте, или же не отвечено на все тесты
function is_valid_test_answ(array $test_answ, array $test): bool
{
    //Ответ пользователя число - номер варианта ответа, и никак иначе
    foreach ($test_answ as $elem) {
        if (gettype($elem) !== 'integer') {
            return false;
        }
    }
    //TODO:Проверить нет ли не числовых индексов

    //Один вопрос - один ответ.На каждый вопрос должен быть ответ.
    if (count($test_answ) !== count($test['questions'])) {
        return false;
    }

    for ($i = 0; $i < count($test_answ); $i++) {
        $answer_ind = $test_answ[$i];
        if ($answer_ind < 0 || $answer_ind >= count($test['questions'][$i]['answers'])) {
            return false;
        }
    }
    return true;
}

function get_test_result(array $test_answ, array $test): string
{
    //count score
    $score = 0;
    for ($i = 0; $i < count($test_answ); $i++) {
        $answer_ind = $test_answ[$i];
        $score += $test['questions'][$i]['answers'][$answer_ind]['points'];
    }
    //get result msg for this score
    for ($i = 0; $i < count($test['results']); $i++) {
        $result = $test['results'][$i];
        //Если последний результат(они упорядочены), то он соотвеств (число,+бесконечность]
        if ($score < $result['max-score'] || ($i === count($test['results'] )-1)) {
            return $result['text'];
        }
    }
}





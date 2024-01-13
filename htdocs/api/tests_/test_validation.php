<?php
require '../vendor/autoload.php'; // Подключаем autoload для Composer
use JsonSchema\Validator;
use JsonSchema\Constraints\Factory;
$test_scheme = file_get_contents('JSON Schemas/Test_JSON_Scheme.json');
function is_valid_test_struct(stdClass $test):bool{

    //Проверка на соотвествие общей структуре в JSON-схеме
    global $test_scheme;
    $scheme = json_decode($test_scheme, true);
    $validator = new Validator;
    $validator->validate($test, $scheme);
    if (!$validator->isValid()) {
        return false;
    }
    //Если меньше 2-ух вопросов или ни дан не один резуьтат теста
    if ( count($test->questions) < 2 || count($test->results ) == 0 ){
        return false;
    }
    //На каждый вопрос не менее 2-ух ответов
    foreach ($test->questions as $question){
        if (count($question->answers)<2){
            return false;
        }
    }
    if(count($test->results)==1) {
        return (($test->results[0]->{'min-score'} === $test->results[0]->{'max-score'}) &&
            $test->results[0]->{'max-score'} !== null);
    } else{
        $start_i = 0;
        $end_i = count($test->results)-1;
        for ($i=$start_i;$i<=$end_i;$i++){
            $result = $test->results[$i];
            if ($i===$start_i){
                if ($result->{'min-score'}!==null || $result->{'max-score'} !== $test->results[$i+1]->{'min-score'}){
                    return  false;
                }
            } elseif ($i===$end_i){
                if ($result->{'max-score'}!==null){
                    return  false;
                }
            } else{
                if ($result->{'max-score'}!== $test->results[$i+1]->{'min-score'}){
                    return  false;
                }
            }
        }
        return true;
    }



}

function is_valid_test_answ_struct(array $test_answ):bool{

}


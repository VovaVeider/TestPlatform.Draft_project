<?php


function validateJson($json)
{
    json_decode($json);
    return json_last_error() === JSON_ERROR_NONE;
}

function calculateTestResult($testJson, $answersJson)
{
    // Проверяем валидность JSON
    if (!validateJson($testJson) || !validateJson($answersJson)) {
        return "Некорректный JSON";
    }

    // Декодируем JSON-строки
    $testData = json_decode($testJson, true);
    $answersData = json_decode($answersJson, true);

    // Проверяем наличие необходимых ключей
    if (!isset($testData['results']) || !isset($answersData['questions']) || !isset($answersData['results'])) {
        return "Отсутствуют необходимые ключи в JSON";
    }

    // Проверяем, что результаты теста являются массивом целых чисел
    if (!is_array($testData['results']) || !array_reduce($testData['results'], function ($carry, $item) {
            return $carry && is_int($item);
        }, true)) {
        return "Результаты теста должны быть массивом целых чисел";
    }

    // Подсчитываем баллы
    $totalPoints = 0;
    foreach ($testData['results'] as $testNumber => $answerIndex) {
        if (
            isset($answersData['questions'][$testNumber]['answers'][$answerIndex]['points'])
            && is_int($answersData['questions'][$testNumber]['answers'][$answerIndex]['points'])
        ) {
            $totalPoints += $answersData['questions'][$testNumber]['answers'][$answerIndex]['points'];
        } else {
            // Если вариант ответа или его баллы не существуют, возвращаем false
            return false;
        }
    }

    // Определяем категорию результатов
    $resultCategory = null;
    foreach ($answersData['results'] as $result) {
        $minScore = isset($result['min-score']) ? $result['min-score'] : PHP_INT_MIN;
        $maxScore = isset($result['max-score']) ? $result['max-score'] : PHP_INT_MAX;

        if ($totalPoints >= $minScore && $totalPoints <= $maxScore) {
            $resultCategory = $result['text'];
            break;
        }
    }

    return $resultCategory;
}

// Пример использования
$testJson = '{
    "results": [1, 1, 2]
}';

$answersJson = '{
    "questions": [
        {
            "text": "В каком городе родился А.П.Чехов?",
            "answers": [
                {"text": "Новиград", "points": -10},
                {"text": "Санкт-Петербург", "points": -22},
                {"text": "Таганрог", "points": 200}
            ]
        },
        {
            "text": "Кто написал Войну и мир?",
            "answers": [
                {"text": "А.П.Чехов", "points": -10},
                {"text": "Глеб Жиглов", "points": -1111},
                {"text": "Л.Н.Толстой", "points": 200}
            ]
        }
    ],
    "results": [
        {"text": "Плохо-очень плохо!", "min-score": null, "max-score": 10},
        {"text": "Приемлимо", "min-score": 10, "max-score": 300},
        {"text": "Превосходно", "min-score": 300, "max-score": null}
    ]
}';

$result = calculateTestResult($testJson, $answersJson);

if ($result === false) {
    echo "Ошибка: Невозможно определить результат тестирования.";
} else {
    echo "Результат тестирования: " . $result;
}

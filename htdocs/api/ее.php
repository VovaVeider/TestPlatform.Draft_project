<?php
require 'vendor/autoload.php'; // Подключаем autoload для Composer

use JsonSchema\Validator;
use JsonSchema\Constraints\Factory;

// Ваш JSON-документ
$jsonDocument = '{
    "name": "John",
    "age": 30,
    "email": "john@example.com"
}';

// Ваша JSON-схема
$jsonSchema = '{
    "type": "object",
    "properties": {
        "name": {"type": "string"},
        "age": {"type": "integer"},
        "email": {"type": "string", "format": "email"}
    },
    "required": ["name", "age", "email"]
}';

// Преобразуем JSON-схему в ассоциативный массив
$schemaData = json_decode($jsonSchema);

// Преобразуем JSON-документ в ассоциативный массив
$jsonData = json_decode($jsonDocument);

// Создаем валидатор
$validator = new Validator;

// Добавляем ограничения с помощью схемы
$validator->validate($jsonData, $schemaData, Factory::buildConstraints());

// Проверяем, прошла ли валидация
if ($validator->isValid()) {
    echo "JSON соответствует схеме.\n";
} else {
    echo "JSON не соответствует схеме. Ошибки:\n";
    foreach ($validator->getErrors() as $error) {
        echo sprintf("[%s] %s\n", $error['property'], $error['message']);
    }
}
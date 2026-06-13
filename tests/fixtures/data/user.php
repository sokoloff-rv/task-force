<?php

$passwordHash = Yii::$app->security->generatePasswordHash('demo');

return [
    'customer' => [
        'id' => 1,
        'name' => 'Иван Заказчик',
        'email' => 'customer@example.com',
        'password' => $passwordHash,
        'role' => 'customer',
        'city_id' => 1,
        'succesful_tasks' => 0,
        'failed_tasks' => 0,
        'hidden_contacts' => 0,
        'total_score' => 0,
    ],
    'executor' => [
        'id' => 2,
        'name' => 'Пётр Исполнитель',
        'email' => 'executor@example.com',
        'password' => $passwordHash,
        'role' => 'executor',
        'city_id' => 1,
        'succesful_tasks' => 0,
        'failed_tasks' => 0,
        'hidden_contacts' => 0,
        'total_score' => 0,
    ],
    'demo' => [
        'id' => 3,
        'name' => 'Демо Пользователь',
        'email' => 'demo@example.com',
        'password' => $passwordHash,
        'role' => 'executor',
        'city_id' => 2,
        'succesful_tasks' => 0,
        'failed_tasks' => 0,
        'hidden_contacts' => 1,
        'total_score' => 0,
    ],
    'busy' => [
        'id' => 4,
        'name' => 'Сергей Занятый',
        'email' => 'busy@example.com',
        'password' => $passwordHash,
        'role' => 'executor',
        'city_id' => 1,
        'succesful_tasks' => 0,
        'failed_tasks' => 0,
        'hidden_contacts' => 0,
        'total_score' => 0,
    ],
];

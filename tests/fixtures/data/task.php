<?php

use Taskforce\Models\Task;

return [
    // Новое задание заказчика (id=1) без исполнителя.
    'new' => [
        'id' => 1,
        'customer_id' => 1,
        'title' => 'Выгулять собаку',
        'description' => 'Нужно выгулять собаку вечером',
        'category_id' => 1,
        'city_id' => 1,
        'status' => Task::STATUS_NEW,
        'executor_id' => null,
    ],
    // Задание в работе: исполнитель id=4 («Занятый») занят им.
    'working' => [
        'id' => 2,
        'customer_id' => 1,
        'title' => 'Убрать квартиру',
        'description' => 'Генеральная уборка двухкомнатной квартиры',
        'category_id' => 2,
        'city_id' => 1,
        'status' => Task::STATUS_WORKING,
        'executor_id' => 4,
    ],
];

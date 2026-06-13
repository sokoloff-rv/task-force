<?php

use app\models\Response;

return [
    'new' => [
        'id' => 1,
        'executor_id' => 2,
        'task_id' => 1,
        'comment' => 'Готов выполнить',
        'price' => 1000,
        'status' => Response::STATUS_NEW,
    ],
];

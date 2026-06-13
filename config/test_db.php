<?php

$db = [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;dbname=taskforce_test',
    'username' => '',
    'password' => '',
    'charset' => 'utf8',
];

$localDb = __DIR__ . '/db.php';
if (is_file($localDb)) {
    $db = array_merge($db, require $localDb);
    $db['dsn'] = 'mysql:host=localhost;dbname=taskforce_test';
}

$localTestDb = __DIR__ . '/test_db-local.php';
if (is_file($localTestDb)) {
    $db = array_merge($db, require $localTestDb);
}

$db['dsn'] = getenv('TEST_DB_DSN') ?: $db['dsn'];
$db['username'] = getenv('TEST_DB_USER') ?: $db['username'];
$db['password'] = getenv('TEST_DB_PASSWORD') ?: $db['password'];

return $db;

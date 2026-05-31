<?php

/**
 * Общие параметры приложения.
 *
 * Секреты (ключи API, секрет приложения VK, ключ валидации cookie) в репозитории
 * не хранятся. Поместите их в config/params-local.php — этот файл исключён из Git.
 * Шаблон с перечнем необходимых параметров: config/params-local.php.example.
 */

$params = [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    // Значения по умолчанию переопределяются в config/params-local.php
    'cookieValidationKey' => '',
    'yandexGeocoderApiKey' => '',
    'yandexMapsApiKey' => '',
    'vkClientId' => '',
    'vkClientSecret' => '',
];

$localParams = __DIR__ . '/params-local.php';
if (is_file($localParams)) {
    $params = array_merge($params, require $localParams);
}

return $params;

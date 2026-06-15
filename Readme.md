# Taskforce
![PHP Version](https://img.shields.io/badge/php-%5E7.4-7A86B8)
![MySQL Version](https://img.shields.io/badge/mysql-%5E5.6-F29221)
![Yii2 Version](https://img.shields.io/badge/Yii2-%5E2.0.45-83C933)
![PHPUnit Version](https://img.shields.io/badge/phpunit-%5E9.5-3A97D0)

## О проекте

«TaskForce» — это онлайн площадка для поиска исполнителей на разовые задачи. Сайт функционирует как биржа объявлений, где заказчики — физические лица публикуют задания. Исполнители могут откликаться на эти задания, предлагая свои услуги и стоимость работ. Проект разработан на базе фреймворка Yii2, использует методологию ООП и паттерн MVC. Также используется внешний API Яндекс.Карт и авторизация через VK ID.

Демонстрационная версия доступна по адресу https://taskforce.sokoloff-rv.ru/.

Для входа в **демо-аккаунт заказчика** используйте следующие данные:

- Логин: customer@fake.mail
- Пароль: democustomer

Для входа в **демо-аккаунт исполнителя** используйте следующие данные:

- Логин: executor@fake.mail
- Пароль: demoexecutor

## Функциональность

Основные возможности, реализованные в проекте:

- Регистрация на сайте;
- Авторизация;
- Регистрация и авторизация через социальную сеть VK;
- Профиль пользователя, отображаемый на отдельной странице:
    - аватар,
    - город,
    - возраст,
    - информация о пользователе,
    - блок с контактами,
    - специализации (для исполнителей),
    - отзывы заказчиков (для исполнителей),
    - статистика выполненных заказов (для исполнителей),
    - дата регистрации (для исполнителей),
    - место в общем рейтинге (для исполнителей),
    - статус (для исполнителей);
- Редактирование профиля пользователя:
    - аватар,
    - электронная почта,
    - день рождения,
    - номер телефона,
    - telegram,
    - информация о себе,
    - пароль (с подтверждением старого);
- Вывод списка заданий с пагинацией (каждый пользователь видит только задания из своего города, а также задания в формате удаленной работы);
- Фильтрация заданий по категориям, времени размещения, наличию откликов и формату работы (удаленная работа или обычная);
- Вывод карточки задания;
- Статусы заданий (в зависимости от того, есть ли на задание отклики, находится ли задание в работе, выполнено оно или провалено);
- Отображение локации задания на Яндекс.Карте через внешний API;
- Возможность оставить отклик на задание (для исполнителей);
- Просмотр откликов на задания (для заказчиков);
- Раздел “Мои задания” для заказчиков:
    - список заданий, находящихся в процессе,
    - список просроченных заданий,
    - список завершенных заданий;
- Раздел “Мои задания” для исполнителей:
    - список новых заданий, по которым ещё не выбран исполнитель,
    - список заданий, над которыми работают исполнители,
    - список завершенных заданий;
- Страница с формой добавления нового задания (для заказчиков), включающая в себя следующие поля:
    - заголовок,
    - описание задания,
    - категория задания,
    - локация,
    - бюджет,
    - срок исполнения,
    - файлы задания;
- Размещение откликов на задания (для исполнителей);
- Выбор исполнителя на задание (для заказчиков);
- Размещение отзывов на исполнителей (для заказчиков);
- Система рейтинга исполнителей;
- Валидация всех форм;
- Возврат страницы с ошибкой 404, если пользователь пытается открыть страницу с несуществующим пользователем или заданием.

## Обзор проекта

[![Видео](https://sokoloff-rv.ru/share/github/taskforce.webp?ver_1)](https://youtu.be/mDWVi3cPgNI)

## Начало работы

Чтобы развернуть проект локально или на хостинге, выполните последовательно несколько действий:

1. Клонируйте репозиторий:

```bash
git clone https://github.com/sokoloff-rv/task-force.git taskforce
```

2. Перейдите в директорию проекта:

```bash
cd taskforce
```

3. Установите зависимости, выполнив команду:

```bash
composer install
```

4. Настройте веб-сервер таким образом, чтобы корневая директория указывала на папку web внутри проекта. Например, в случае с размещением проекта в директории `public_html` это можно сделать с помощью команды:

```bash
ln -s web public_html
```

5. Создайте базу данных для проекта, используя схему из файла `schema.sql`:

```sql
CREATE DATABASE taskforce
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_general_ci;

USE taskforce;

/* Города */
CREATE TABLE cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    latitude DECIMAL(11, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL
);

/* Пользователи */
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    birthday DATETIME,
    phone VARCHAR(100),
    telegram VARCHAR(100),
    information TEXT,
    specializations VARCHAR(255),
    avatar VARCHAR(255),
    register_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    role ENUM ('customer', 'executor') NOT NULL,
    succesful_tasks INT,
    failed_tasks INT,
    city_id INT,
    vk_id INT,
    hidden_contacts INT DEFAULT 0 NOT NULL,
    total_score FLOAT DEFAULT 0 NOT NULL,
    FOREIGN KEY (city_id) REFERENCES cities(id)
);

/* Категории заданий */
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    alias VARCHAR(100) NOT NULL
);

/* Задания */
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    city_id INT,
    budget VARCHAR(100),
    deadline DATETIME,
    location VARCHAR(255),
    latitude DECIMAL(11, 8),
    longitude DECIMAL(11, 8),
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'new' NOT NULL,
    executor_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (executor_id) REFERENCES users(id),
    FOREIGN KEY (city_id) REFERENCES cities(id)
);

/* Файлы */
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    link VARCHAR(255) NOT NULL UNIQUE,
    task_id INT NOT NULL,
    FOREIGN KEY (task_id) REFERENCES tasks(id)
);

/* Отклики */
CREATE TABLE responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    executor_id INT NOT NULL,
    task_id INT NOT NULL,
    comment TEXT,
    price INT,
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'new' NOT NULL,
    FOREIGN KEY (executor_id) REFERENCES users(id),
    FOREIGN KEY (task_id) REFERENCES tasks(id)
);

/* Отзывы */
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    executor_id INT NOT NULL,
    task_id INT NOT NULL,
    comment TEXT,
    grade INT,
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (executor_id) REFERENCES users(id),
    FOREIGN KEY (task_id) REFERENCES tasks(id)
);

CREATE FULLTEXT INDEX task_title_search ON tasks(title);
CREATE FULLTEXT INDEX task_description_search ON tasks(description);
```

6. Настройте подключение к базе данных в файле `config\db.php`, указав в нем параметры своего окружения. Например, это может выглядеть так:

```php
<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=taskforce',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
];
```

7. Задайте секреты окружения. Они не хранятся в репозитории, поэтому скопируйте шаблон `config/params-local.php.example` в `config/params-local.php` и заполните значениями своего окружения:

```bash
cp config/params-local.php.example config/params-local.php
```

```php
<?php

return [
    // Ключ валидации cookie — любая случайная строка. Обязателен.
    'cookieValidationKey' => '',
    // Ключ HTTP Geocoder API Яндекс.Карт (используется на сервере).
    'yandexGeocoderApiKey' => '',
    // Ключ JavaScript API Яндекс.Карт (используется на клиенте).
    'yandexMapsApiKey' => '',
    // Идентификатор и защищённый ключ приложения VK ID.
    'vkClientId' => '',
    'vkClientSecret' => '',
];
```

Где взять значения:

- `cookieValidationKey` — любая случайная строка, например результат `openssl rand -base64 32`;
- `yandexGeocoderApiKey` и `yandexMapsApiKey` — ключи [HTTP Geocoder API и JavaScript API](https://developer.tech.yandex.ru/services/) в кабинете разработчика Яндекс.Карт;
- `vkClientId` и `vkClientSecret` — идентификатор и защищённый ключ приложения, созданного в [кабинете VK ID](https://id.vk.com/about/business/go/). Авторизация выполняется по актуальному протоколу VK ID (OAuth 2.1); в настройках приложения VK укажите доверенный redirect URL вида `https://<ваш-домен>/auth/login`.

## Тестирование

Проект покрыт автотестами [Codeception](https://codeception.com/). Тесты делятся на две группы:

- модульные тесты доменной логики (`tests/unit/src`), ресурсов и компонентов — работают без базы данных;
- интеграционные тесты моделей, форм и контроллеров (`tests/unit/models`, `tests/unit/controllers`) и functional-тесты HTTP-сценариев (`tests/functional`) — работают с отдельной тестовой базой через фикстуры (`tests/fixtures`).

Логин и пароль тестового пользователя прописываютсяя в `config/test_db-local.php` (см. шаблон `config/test_db-local.php.example`).

Порядок действий:

1. Скопируйте шаблон и впишите учётные данные тестового пользователя:

```bash
cp config/test_db-local.php.example config/test_db-local.php
```

2. Загрузите схему таблиц в тестовую базу:

```bash
bash tests/init-test-db.sh
```

3. Запустите полный набор тестов:

```bash
composer test
```

Обычный запуск завершится ошибкой, если тестовая БД недоступна: это не позволяет CI случайно пропустить интеграционные тесты. Для локального запуска только доступных без БД тестов можно явно разрешить пропуски:

```bash
TEST_DB_OPTIONAL=1 php vendor/bin/codecept run unit
```

Для отчёта о покрытии установите Xdebug или PCOV и выполните:

```bash
composer test:coverage
```

В GitHub Actions тестовая MySQL создаётся автоматически. Параметры подключения также можно передать через переменные `TEST_DB_DSN`, `TEST_DB_USER` и `TEST_DB_PASSWORD`.

## Техническое задание

[Посмотреть техническое задание проекта](tz.md)

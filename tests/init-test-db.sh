#!/usr/bin/env bash

set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

read -r DB_HOST DB_NAME DB_USER DB_PASS < <(php -r '
    $db = require "config/test_db.php";
    preg_match("/host=([^;]+)/", $db["dsn"], $h);
    preg_match("/dbname=([^;]+)/", $db["dsn"], $n);
    echo ($h[1] ?? "localhost") . " " . ($n[1] ?? "taskforce_test") . " "
        . $db["username"] . " " . $db["password"] . "\n";
')

echo "Загружаю схему в базу '${DB_NAME}' на хосте '${DB_HOST}' (пользователь '${DB_USER}')..."

mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < tests/_data/schema-test.sql

echo "Готово: схема загружена в тестовую базу '${DB_NAME}'."

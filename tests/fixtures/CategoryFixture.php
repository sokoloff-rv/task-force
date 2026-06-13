<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Фикстура категорий заданий.
 */
class CategoryFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Category';
    public $dataFile = '@app/tests/fixtures/data/category.php';
}

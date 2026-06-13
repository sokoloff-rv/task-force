<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Фикстура заданий.
 */
class TaskFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Task';
    public $dataFile = '@app/tests/fixtures/data/task.php';
    public $depends = [UserFixture::class, CategoryFixture::class, CityFixture::class];
}

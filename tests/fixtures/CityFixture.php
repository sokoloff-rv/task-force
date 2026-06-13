<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Фикстура городов.
 */
class CityFixture extends ActiveFixture
{
    public $modelClass = 'app\models\City';
    public $dataFile = '@app/tests/fixtures/data/city.php';
}

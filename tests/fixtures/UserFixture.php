<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Фикстура пользователей.
 */
class UserFixture extends ActiveFixture
{
    public $modelClass = 'app\models\User';
    public $dataFile = '@app/tests/fixtures/data/user.php';
    public $depends = [CityFixture::class];
}

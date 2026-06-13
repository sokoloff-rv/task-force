<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

class ResponseFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Response';
    public $dataFile = '@app/tests/fixtures/data/response.php';
    public $depends = [UserFixture::class, TaskFixture::class];
}

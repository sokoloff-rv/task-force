<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

class ReviewFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Review';
    public $dataFile = '@app/tests/fixtures/data/review.php';
    public $depends = [UserFixture::class, TaskFixture::class];
}

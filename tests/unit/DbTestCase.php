<?php

namespace tests\unit;

use Codeception\Test\Unit;
use tests\fixtures\CityFixture;
use tests\fixtures\CategoryFixture;
use tests\fixtures\UserFixture;
use tests\fixtures\TaskFixture;
use tests\fixtures\ResponseFixture;
use tests\fixtures\ReviewFixture;
use Yii;

abstract class DbTestCase extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        try {
            Yii::$app->db->open();
        } catch (\Throwable $error) {
            $message = 'Тестовая БД недоступна: ' . $error->getMessage()
                . '. Создайте базу taskforce_test и выполните bash tests/init-test-db.sh.';

            if (getenv('TEST_DB_OPTIONAL') === '1') {
                $this->markTestSkipped($message);
            }

            $this->fail($message);
        }

        $this->tester->haveFixtures($this->fixtures());
    }

    protected function fixtures(): array
    {
        return [
            'cities' => CityFixture::class,
            'categories' => CategoryFixture::class,
            'users' => UserFixture::class,
            'tasks' => TaskFixture::class,
            'responses' => ResponseFixture::class,
            'reviews' => ReviewFixture::class,
        ];
    }
}

<?php

namespace tests\unit\controllers;

use app\controllers\AuthController;
use app\models\User;
use tests\unit\DbTestCase;
use Yii;
use yii\base\Component;

class AuthControllerTest extends DbTestCase
{
    private $originalCollection;

    protected function _before()
    {
        parent::_before();
        $this->originalCollection = Yii::$app->get('authClientCollection');
        Yii::$app->request->setQueryParams(['code' => 'auth-code', 'device_id' => 'device-id']);
    }

    protected function _after()
    {
        Yii::$app->set('authClientCollection', $this->originalCollection);
        Yii::$app->request->setQueryParams([]);
        Yii::$app->session->remove('vkRegistration');
        Yii::$app->user->logout();
    }

    public function testExistingVkUserIsLoggedInAndRedirectedToTasks(): void
    {
        $user = User::findOne(2);
        $user->vk_id = 42;
        $user->save(false);
        $this->installVkClient([
            'user_id' => 42,
            'email' => 'executor@example.com',
        ]);

        $response = (new AuthController('auth', Yii::$app))->actionLogin();

        verify(Yii::$app->user->id)->equals(2);
        verify($response->headers->get('Location'))->stringEndsWith('/tasks');
        verify($response->headers->get('Location'))->stringNotContainsString('/auth/tasks');
    }

    public function testNewVkUserWithoutEmailIsRedirectedToCompletionForm(): void
    {
        $this->installVkClient([
            'user_id' => 100,
            'first_name' => 'Новый',
            'last_name' => 'VK',
            'avatar' => str_repeat('a', 300),
        ]);

        $response = (new AuthController('auth', Yii::$app))->actionLogin();

        verify(User::findOne(['vk_id' => 100]))->empty();
        verify(Yii::$app->session->get('vkRegistration'))->notEmpty();
        verify($response->headers->get('Location'))->stringEndsWith('/auth/email');
    }

    public function testNewVkUserIsCreatedLoggedInAndRedirectedToTasks(): void
    {
        $this->installVkClient([
            'user_id' => 99,
            'first_name' => 'Новый',
            'last_name' => 'VK',
            'email' => 'new-vk@example.com',
        ]);

        $response = (new AuthController('auth', Yii::$app))->actionLogin();

        $user = User::findOne(['vk_id' => 99]);
        verify($user)->notEmpty();
        verify(Yii::$app->user->id)->equals($user->id);
        verify($response->headers->get('Location'))->stringEndsWith('/tasks');
        verify($response->headers->get('Location'))->stringNotContainsString('/auth/tasks');
    }

    private function installVkClient(array $attributes): void
    {
        $client = new class($attributes) {
            private array $attributes;

            public function __construct(array $attributes)
            {
                $this->attributes = $attributes;
            }

            public function fetchAccessToken($code, array $params)
            {
                verify($code)->equals('auth-code');
                verify($params)->equals(['device_id' => 'device-id']);
                return new \stdClass();
            }

            public function getUserAttributes(): array
            {
                return $this->attributes;
            }
        };

        Yii::$app->set('authClientCollection', new class($client) extends Component {
            private $client;

            public function __construct($client, $config = [])
            {
                $this->client = $client;
                parent::__construct($config);
            }

            public function getClient($id)
            {
                verify($id)->equals('vkid');
                return $this->client;
            }
        });
    }
}

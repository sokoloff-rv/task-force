<?php

namespace tests\unit\models;

use app\models\User;
use tests\unit\DbTestCase;
use Yii;

class UserTest extends DbTestCase
{
    protected function _after()
    {
        Yii::$app->user->logout();
    }

    public function testFindIdentityById()
    {
        verify($user = User::findIdentity(1))->notEmpty();
        verify($user->name)->equals('Иван Заказчик');

        verify(User::findIdentity(99999))->empty();
    }

    public function testFindIdentityByAccessTokenIsNotImplemented()
    {
        verify(User::findIdentityByAccessToken('any-token'))->null();
    }

    public function testSessionAuthKeyIsValidated()
    {
        $user = User::findIdentity(1);

        verify($user->getAuthKey())->null();
        verify($user->validateAuthKey(null))->true();
        verify($user->validateAuthKey('whatever'))->false();
    }

    public function testValidatePassword()
    {
        $user = User::findIdentity(3);

        verify($user->validatePassword('demo'))->true();
        verify($user->validatePassword('wrong-password'))->false();
    }

    public function testGetIdReturnsPrimaryKey()
    {
        $user = User::findIdentity(2);

        verify($user->getId())->equals(2);
    }

    public function testGetCurrentUserReturnsLoggedInUser()
    {
        $user = User::findIdentity(2);
        Yii::$app->user->login($user);

        verify(User::getCurrentUser()->id)->equals(2);
    }

    public function testUserStatusReflectsActiveTask()
    {
        verify(User::findIdentity(4)->getUserStatus())->equals('Занят');
        verify(User::findIdentity(2)->getUserStatus())->equals('Открыт для новых заказов');
    }
}

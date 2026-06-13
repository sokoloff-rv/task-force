<?php

namespace tests\unit\models\forms;

use app\models\forms\RegistrationForm;
use app\models\User;
use tests\unit\DbTestCase;
use Yii;

class RegistrationFormTest extends DbTestCase
{
    protected function _after()
    {
        Yii::$app->user->logout();
    }

    public function testCreateUserReturnsFalseOnEmptyForm()
    {
        $form = new RegistrationForm();

        verify($form->createUser())->false();
        verify(Yii::$app->user->isGuest)->true();
    }

    public function testCreateUserReturnsFalseOnDuplicateEmail()
    {
        $form = new RegistrationForm();
        $form->name = 'Дубликат';
        $form->email = 'customer@example.com';
        $form->city = '1';
        $form->password = 'secret123';
        $form->passwordRepeat = 'secret123';

        verify($form->createUser())->false();
        verify($form->errors)->arrayHasKey('email');
    }

    public function testCreateUserReturnsFalseOnPasswordMismatch()
    {
        $form = new RegistrationForm();
        $form->name = 'Новый';
        $form->email = 'newcomer@example.com';
        $form->city = '1';
        $form->password = 'secret123';
        $form->passwordRepeat = 'another456';

        verify($form->createUser())->false();
        verify($form->errors)->arrayHasKey('passwordRepeat');
    }

    public function testCreateUserPersistsAndLogsInOnSuccess()
    {
        $form = new RegistrationForm();
        $form->name = 'Новый Пользователь';
        $form->email = 'newcomer@example.com';
        $form->city = '1';
        $form->password = 'secret123';
        $form->passwordRepeat = 'secret123';
        $form->isExecutor = true;

        verify($form->createUser())->true();
        verify(Yii::$app->user->isGuest)->false();

        $created = User::findOne(['email' => 'newcomer@example.com']);
        verify($created)->notEmpty();
        verify($created->role)->equals(User::ROLE_EXECUTOR);
        verify($created->validatePassword('secret123'))->true();
    }
}

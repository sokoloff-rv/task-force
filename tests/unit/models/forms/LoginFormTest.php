<?php

namespace tests\unit\models\forms;

use app\models\forms\LoginForm;
use tests\unit\DbTestCase;

class LoginFormTest extends DbTestCase
{
    public function testValidatesWithCorrectCredentials()
    {
        $form = new LoginForm([
            'email' => 'demo@example.com',
            'password' => 'demo',
        ]);

        verify($form->validate())->true();
        verify($form->getUser()->email)->equals('demo@example.com');
    }

    public function testFailsWithWrongPassword()
    {
        $form = new LoginForm([
            'email' => 'demo@example.com',
            'password' => 'wrong-password',
        ]);

        verify($form->validate())->false();
        verify($form->errors)->arrayHasKey('password');
    }

    public function testFailsWithUnknownEmail()
    {
        $form = new LoginForm([
            'email' => 'ghost@example.com',
            'password' => 'demo',
        ]);

        verify($form->validate())->false();
        verify($form->getUser())->null();
    }

    public function testRequiresEmailAndPassword()
    {
        $form = new LoginForm();

        verify($form->validate())->false();
        verify($form->errors)->arrayHasKey('email');
        verify($form->errors)->arrayHasKey('password');
    }
}

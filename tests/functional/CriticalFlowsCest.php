<?php

use app\models\User;
use tests\fixtures\CategoryFixture;
use tests\fixtures\CityFixture;
use tests\fixtures\TaskFixture;
use tests\fixtures\UserFixture;

class CriticalFlowsCest
{
    public function _before(FunctionalTester $I): void
    {
        $I->haveFixtures([
            'cities' => CityFixture::class,
            'categories' => CategoryFixture::class,
            'users' => UserFixture::class,
            'tasks' => TaskFixture::class,
        ]);
    }

    public function guestIsRedirectedFromSecuredPage(FunctionalTester $I): void
    {
        $I->amOnRoute('tasks/index');
        $I->seeInCurrentUrl('/landing');
    }

    public function authenticatedUserIsRedirectedFromLanding(FunctionalTester $I): void
    {
        $I->amLoggedInAs(User::findOne(2));
        $I->amOnRoute('landing/index');
        $I->seeInCurrentUrl('/tasks');
    }

    public function loginRedirectsToAbsoluteTasksRoute(FunctionalTester $I): void
    {
        $I->amOnRoute('landing/index');
        $I->submitForm('#login-form', [
            'LoginForm[email]' => 'demo@example.com',
            'LoginForm[password]' => 'demo',
        ]);

        $I->seeInCurrentUrl('/tasks');
        $I->dontSeeInCurrentUrl('/landing/tasks');
    }

    public function registrationRedirectsToAbsoluteTasksRoute(FunctionalTester $I): void
    {
        $I->amOnRoute('registration/index');
        $I->submitForm('#registration-form', [
            'RegistrationForm[name]' => 'Новый Пользователь',
            'RegistrationForm[email]' => 'functional@example.com',
            'RegistrationForm[city]' => '1',
            'RegistrationForm[password]' => 'secret123',
            'RegistrationForm[passwordRepeat]' => 'secret123',
            'RegistrationForm[isExecutor]' => '1',
        ]);

        $I->seeInCurrentUrl('/tasks');
        $I->dontSeeInCurrentUrl('/registration/tasks');
        $I->seeRecord(User::class, ['email' => 'functional@example.com']);
    }

    public function executorCannotOpenNewTaskPage(FunctionalTester $I): void
    {
        $I->amLoggedInAs(User::findOne(2));
        $I->amOnRoute('tasks/new');

        $I->seeInCurrentUrl('/tasks');
        $I->dontSeeInCurrentUrl('/tasks/new');
        $I->dontSee('Опишите суть работы');
    }

    public function mapApiIsRenderedBeforeInlineMapInitialization(FunctionalTester $I): void
    {
        $I->amLoggedInAs(User::findOne(2));
        $I->amOnRoute('tasks/view', ['id' => 1]);

        $source = $I->grabPageSource();
        $apiPosition = strpos($source, 'api-maps.yandex.ru');
        $initializationPosition = strpos($source, 'ymaps.ready(init)');

        verify($apiPosition)->notFalse();
        verify($initializationPosition)->notFalse();
        verify($apiPosition)->lessThan($initializationPosition);
    }

    public function vkRegistrationCanBeCompletedWithEmail(FunctionalTester $I): void
    {
        Yii::$app->session->set('vkRegistration', [
            'user_id' => 777777,
            'first_name' => 'Новый',
            'last_name' => 'Пользователь',
            'avatar' => 'https://example.com/' . str_repeat('a', 300),
        ]);

        $I->amOnRoute('auth/email');
        $I->see('Для завершения регистрации укажите свой email.');
        $I->submitForm('#vk-email-form', [
            'VkEmailForm[email]' => 'new-vk@example.com',
        ], 'Завершить регистрацию');

        $I->seeInCurrentUrl('/tasks');
        $I->seeRecord(User::class, [
            'email' => 'new-vk@example.com',
            'vk_id' => 777777,
            'role' => User::ROLE_CUSTOMER,
        ]);
        verify(Yii::$app->session->get('vkRegistration'))->null();
    }

    public function vkRegistrationRejectsExistingEmail(FunctionalTester $I): void
    {
        Yii::$app->session->set('vkRegistration', [
            'user_id' => 888888,
            'first_name' => 'Другой',
            'last_name' => 'Пользователь',
        ]);

        $I->amOnRoute('auth/email');
        $I->submitForm('#vk-email-form', [
            'VkEmailForm[email]' => 'customer@example.com',
        ], 'Завершить регистрацию');

        $I->see('Этот email уже используется.');
        $I->dontSeeRecord(User::class, ['vk_id' => 888888]);
    }
}

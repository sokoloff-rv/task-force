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
}

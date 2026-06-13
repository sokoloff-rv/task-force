<?php

namespace tests\unit\src\Actions;

use Codeception\Test\Unit;
use Taskforce\Models\Task;
use Taskforce\Actions\ActionCancel;
use Taskforce\Actions\ActionAccept;
use Taskforce\Actions\ActionRespond;
use Taskforce\Actions\ActionDeny;

class ActionsTest extends Unit
{
    private const ID_CUSTOMER = 1;
    private const ID_EXECUTOR = 2;
    private const ID_STRANGER = 99;

    private function task(string $status): Task
    {
        return new Task(self::ID_CUSTOMER, self::ID_EXECUTOR, $status);
    }

    public function testActionNamesAndTitles(): void
    {
        verify(ActionCancel::getName())->equals('cancel');
        verify(ActionAccept::getName())->equals('accept');
        verify(ActionRespond::getName())->equals('respond');
        verify(ActionDeny::getName())->equals('deny');

        verify(ActionCancel::getTitle())->equals('Отменить');
        verify(ActionAccept::getTitle())->equals('Принять');
        verify(ActionRespond::getTitle())->equals('Откликнуться');
        verify(ActionDeny::getTitle())->equals('Отказаться');
    }

    public function testRespondAllowedForExecutorOnNewTask(): void
    {
        $action = new ActionRespond();

        verify($action->checkRight($this->task(Task::STATUS_NEW), self::ID_EXECUTOR))->true();
    }

    public function testRespondDeniedForCustomerAndOnWrongStatus(): void
    {
        $action = new ActionRespond();

        verify($action->checkRight($this->task(Task::STATUS_NEW), self::ID_CUSTOMER))->false();
        verify($action->checkRight($this->task(Task::STATUS_NEW), self::ID_STRANGER))->false();
        verify($action->checkRight($this->task(Task::STATUS_WORKING), self::ID_EXECUTOR))->false();
    }

    public function testCancelAllowedForCustomerOnNewTask(): void
    {
        $action = new ActionCancel();

        verify($action->checkRight($this->task(Task::STATUS_NEW), self::ID_CUSTOMER))->true();
    }

    public function testCancelDeniedForExecutorAndOnWrongStatus(): void
    {
        $action = new ActionCancel();

        verify($action->checkRight($this->task(Task::STATUS_NEW), self::ID_EXECUTOR))->false();
        verify($action->checkRight($this->task(Task::STATUS_WORKING), self::ID_CUSTOMER))->false();
    }

    public function testAcceptAllowedForCustomerOnWorkingTask(): void
    {
        $action = new ActionAccept();

        verify($action->checkRight($this->task(Task::STATUS_WORKING), self::ID_CUSTOMER))->true();
    }

    public function testAcceptDeniedForExecutorAndOnWrongStatus(): void
    {
        $action = new ActionAccept();

        verify($action->checkRight($this->task(Task::STATUS_WORKING), self::ID_EXECUTOR))->false();
        verify($action->checkRight($this->task(Task::STATUS_NEW), self::ID_CUSTOMER))->false();
    }

    public function testDenyAllowedForExecutorOnWorkingTask(): void
    {
        $action = new ActionDeny();

        verify($action->checkRight($this->task(Task::STATUS_WORKING), self::ID_EXECUTOR))->true();
    }

    public function testDenyDeniedForCustomerAndOnWrongStatus(): void
    {
        $action = new ActionDeny();

        verify($action->checkRight($this->task(Task::STATUS_WORKING), self::ID_CUSTOMER))->false();
        verify($action->checkRight($this->task(Task::STATUS_NEW), self::ID_EXECUTOR))->false();
    }
}

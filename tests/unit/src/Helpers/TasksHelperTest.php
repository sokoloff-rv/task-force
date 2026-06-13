<?php

namespace tests\unit\src\Helpers;

use Codeception\Test\Unit;
use Taskforce\Helpers\TasksHelper;
use Taskforce\Models\Task;
use app\models\User;

class TasksHelperTest extends Unit
{
    private const ID_CUSTOMER = 1;
    private const ID_EXECUTOR = 2;

    private function response(int $executorId): \stdClass
    {
        return (object) ['executor_id' => $executorId];
    }

    public function testExecutorWithoutResponseSeesResponseButton(): void
    {
        $result = TasksHelper::userCanSeeResponseButton(
            self::ID_EXECUTOR,
            User::ROLE_EXECUTOR,
            Task::STATUS_NEW,
            [$this->response(42)]
        );

        verify($result)->true();
    }

    public function testExecutorWithOwnResponseDoesNotSeeResponseButton(): void
    {
        $result = TasksHelper::userCanSeeResponseButton(
            self::ID_EXECUTOR,
            User::ROLE_EXECUTOR,
            Task::STATUS_NEW,
            [$this->response(self::ID_EXECUTOR)]
        );

        verify($result)->false();
    }

    public function testCustomerDoesNotSeeResponseButton(): void
    {
        $result = TasksHelper::userCanSeeResponseButton(
            self::ID_CUSTOMER,
            User::ROLE_CUSTOMER,
            Task::STATUS_NEW,
            []
        );

        verify($result)->false();
    }

    public function testResponseButtonHiddenOnNonNewTask(): void
    {
        $result = TasksHelper::userCanSeeResponseButton(
            self::ID_EXECUTOR,
            User::ROLE_EXECUTOR,
            Task::STATUS_WORKING,
            []
        );

        verify($result)->false();
    }

    public function testRefusalButtonVisibleForExecutorOnWorkingTask(): void
    {
        verify(TasksHelper::userCanSeeRefusalButton(self::ID_EXECUTOR, Task::STATUS_WORKING, self::ID_EXECUTOR))->true();
        verify(TasksHelper::userCanSeeRefusalButton(self::ID_CUSTOMER, Task::STATUS_WORKING, self::ID_EXECUTOR))->false();
        verify(TasksHelper::userCanSeeRefusalButton(self::ID_EXECUTOR, Task::STATUS_NEW, self::ID_EXECUTOR))->false();
    }

    public function testCompletionButtonVisibleForCustomerOnWorkingTask(): void
    {
        verify(TasksHelper::userCanSeeCompletionButton(self::ID_CUSTOMER, Task::STATUS_WORKING, self::ID_CUSTOMER))->true();
        verify(TasksHelper::userCanSeeCompletionButton(self::ID_EXECUTOR, Task::STATUS_WORKING, self::ID_CUSTOMER))->false();
        verify(TasksHelper::userCanSeeCompletionButton(self::ID_CUSTOMER, Task::STATUS_NEW, self::ID_CUSTOMER))->false();
    }

    public function testCancelButtonVisibleForCustomerOnNewTask(): void
    {
        verify(TasksHelper::userCanSeeCancelButton(self::ID_CUSTOMER, Task::STATUS_NEW, self::ID_CUSTOMER))->true();
        verify(TasksHelper::userCanSeeCancelButton(self::ID_EXECUTOR, Task::STATUS_NEW, self::ID_CUSTOMER))->false();
        verify(TasksHelper::userCanSeeCancelButton(self::ID_CUSTOMER, Task::STATUS_WORKING, self::ID_CUSTOMER))->false();
    }
}

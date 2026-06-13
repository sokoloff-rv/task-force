<?php

namespace tests\unit\src\Models;

use Codeception\Test\Unit;
use Taskforce\Models\Task;
use Taskforce\Actions\ActionCancel;
use Taskforce\Actions\ActionAccept;
use Taskforce\Actions\ActionRespond;
use Taskforce\Actions\ActionDeny;
use Taskforce\Exceptions\ExceptionStatusNotExist;
use Taskforce\Exceptions\ExceptionActionNotExist;
use Taskforce\Exceptions\ExceptionNoActionAvailable;

class TaskTest extends Unit
{
    private const ID_CUSTOMER = 1;
    private const ID_EXECUTOR = 2;

    private function makeTask(string $status = Task::STATUS_NEW): Task
    {
        return new Task(self::ID_CUSTOMER, self::ID_EXECUTOR, $status);
    }

    public function testConstructorStoresParticipants(): void
    {
        $task = $this->makeTask();

        verify($task->getIdCustomer())->equals(self::ID_CUSTOMER);
        verify($task->getIdExecutor())->equals(self::ID_EXECUTOR);
    }

    public function testConstructorDefaultStatusIsNew(): void
    {
        $task = $this->makeTask();

        verify($task->getAvailableActions(Task::STATUS_NEW))->equals([
            ActionCancel::getName(),
            ActionRespond::getName(),
        ]);
    }

    public function testConstructorThrowsOnUnknownStatus(): void
    {
        $this->expectException(ExceptionStatusNotExist::class);

        new Task(self::ID_CUSTOMER, self::ID_EXECUTOR, 'unknown_status');
    }

    public function testStatusesMapContainsAllStatuses(): void
    {
        $map = $this->makeTask()->getStatusesMap();

        verify(array_keys($map))->equals([
            Task::STATUS_NEW,
            Task::STATUS_CANCELLED,
            Task::STATUS_WORKING,
            Task::STATUS_COMPLETED,
            Task::STATUS_FAILED,
        ]);
    }

    /**
     * @dataProvider statusNameProvider
     */
    public function testGetStatusNameReturnsHumanReadableTitle(string $alias, string $expected): void
    {
        verify(Task::getStatusName($alias))->equals($expected);
    }

    public function statusNameProvider(): array
    {
        return [
            [Task::STATUS_NEW, 'Новое'],
            [Task::STATUS_CANCELLED, 'Отменено'],
            [Task::STATUS_WORKING, 'В работе'],
            [Task::STATUS_COMPLETED, 'Выполнено'],
            [Task::STATUS_FAILED, 'Провалено'],
        ];
    }

    public function testActionsMapContainsAllActions(): void
    {
        $map = $this->makeTask()->getActionsMap();

        verify(array_keys($map))->equals([
            ActionCancel::getName(),
            ActionAccept::getName(),
            ActionRespond::getName(),
            ActionDeny::getName(),
        ]);
    }

    /**
     * @dataProvider nextStatusProvider
     */
    public function testGetNextStatusForAction(string $action, string $expectedStatus): void
    {
        verify($this->makeTask()->getNextStatus($action))->equals($expectedStatus);
    }

    public function nextStatusProvider(): array
    {
        return [
            'отмена -> отменено'    => [ActionCancel::getName(), Task::STATUS_CANCELLED],
            'принять -> выполнено'  => [ActionAccept::getName(), Task::STATUS_COMPLETED],
            'отклик -> в работе'    => [ActionRespond::getName(), Task::STATUS_WORKING],
            'отказ -> провалено'    => [ActionDeny::getName(), Task::STATUS_FAILED],
        ];
    }

    public function testGetNextStatusThrowsOnUnknownAction(): void
    {
        $this->expectException(ExceptionActionNotExist::class);

        $this->makeTask()->getNextStatus('fly');
    }

    public function testAvailableActionsForNewStatus(): void
    {
        verify($this->makeTask()->getAvailableActions(Task::STATUS_NEW))->equals([
            ActionCancel::getName(),
            ActionRespond::getName(),
        ]);
    }

    public function testAvailableActionsForWorkingStatus(): void
    {
        verify($this->makeTask()->getAvailableActions(Task::STATUS_WORKING))->equals([
            ActionAccept::getName(),
            ActionDeny::getName(),
        ]);
    }

    /**
     * @dataProvider finalStatusProvider
     */
    public function testAvailableActionsThrowsForFinalStatuses(string $status): void
    {
        $this->expectException(ExceptionNoActionAvailable::class);

        $this->makeTask()->getAvailableActions($status);
    }

    public function finalStatusProvider(): array
    {
        return [
            [Task::STATUS_COMPLETED],
            [Task::STATUS_CANCELLED],
            [Task::STATUS_FAILED],
        ];
    }

    public function testAvailableActionsThrowsForUnknownStatus(): void
    {
        $this->expectException(ExceptionStatusNotExist::class);

        $this->makeTask()->getAvailableActions('unknown_status');
    }
}

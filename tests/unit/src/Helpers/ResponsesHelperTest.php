<?php

namespace tests\unit\src\Helpers;

use Codeception\Test\Unit;
use Taskforce\Helpers\ResponsesHelper;
use Taskforce\Models\Task;
use app\models\Response;

class ResponsesHelperTest extends Unit
{
    private const ID_CUSTOMER = 1;
    private const ID_EXECUTOR = 2;
    private const ID_STRANGER = 99;

    private function response(int $executorId): \stdClass
    {
        return (object) ['executor_id' => $executorId];
    }

    public function testCustomerSeesResponsesList(): void
    {
        $result = ResponsesHelper::userCanSeeResponsesList(
            [$this->response(self::ID_EXECUTOR)],
            self::ID_CUSTOMER,
            self::ID_CUSTOMER
        );

        verify($result)->true();
    }

    public function testExecutorSeesResponsesListWhenHeResponded(): void
    {
        $result = ResponsesHelper::userCanSeeResponsesList(
            [$this->response(self::ID_EXECUTOR)],
            self::ID_EXECUTOR,
            self::ID_CUSTOMER
        );

        verify($result)->true();
    }

    public function testStrangerDoesNotSeeResponsesList(): void
    {
        $result = ResponsesHelper::userCanSeeResponsesList(
            [$this->response(self::ID_EXECUTOR)],
            self::ID_STRANGER,
            self::ID_CUSTOMER
        );

        verify($result)->false();
    }

    public function testEmptyResponsesListIsNotVisible(): void
    {
        verify(ResponsesHelper::userCanSeeResponsesList([], self::ID_CUSTOMER, self::ID_CUSTOMER))->false();
    }

    public function testResponseVisibleForCustomerAndExecutorOnly(): void
    {
        verify(ResponsesHelper::userCanSeeResponse(self::ID_CUSTOMER, self::ID_CUSTOMER, self::ID_EXECUTOR))->true();
        verify(ResponsesHelper::userCanSeeResponse(self::ID_EXECUTOR, self::ID_CUSTOMER, self::ID_EXECUTOR))->true();
        verify(ResponsesHelper::userCanSeeResponse(self::ID_STRANGER, self::ID_CUSTOMER, self::ID_EXECUTOR))->false();
    }

    public function testResponseButtonsVisibleForCustomerOnNewTaskAndResponse(): void
    {
        $result = ResponsesHelper::userCanSeeResponseButtons(
            self::ID_CUSTOMER,
            self::ID_CUSTOMER,
            Task::STATUS_NEW,
            Response::STATUS_NEW
        );

        verify($result)->true();
    }

    public function testResponseButtonsHiddenForExecutorOrProcessedResponse(): void
    {
        verify(ResponsesHelper::userCanSeeResponseButtons(
            self::ID_EXECUTOR,
            self::ID_CUSTOMER,
            Task::STATUS_NEW,
            Response::STATUS_NEW
        ))->false();

        verify(ResponsesHelper::userCanSeeResponseButtons(
            self::ID_CUSTOMER,
            self::ID_CUSTOMER,
            Task::STATUS_NEW,
            Response::STATUS_ACCEPTED
        ))->false();

        verify(ResponsesHelper::userCanSeeResponseButtons(
            self::ID_CUSTOMER,
            self::ID_CUSTOMER,
            Task::STATUS_WORKING,
            Response::STATUS_NEW
        ))->false();
    }
}

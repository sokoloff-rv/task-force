<?php

namespace tests\unit\models;

use app\models\Response;
use app\models\Task;
use app\models\TaskSearch;
use app\models\User;
use Taskforce\Helpers\UsersHelper;
use Taskforce\Models\Task as TaskBasic;
use tests\unit\DbTestCase;
use Yii;

class WorkflowTest extends DbTestCase
{
    protected function _after()
    {
        Yii::$app->user->logout();
        Yii::$app->request->setQueryParams([]);
    }

    public function testTaskAndResponseStatusTransitionsArePersisted(): void
    {
        $task = Task::findOne(1);
        $response = Response::findOne(1);

        verify($response->accept())->true();
        verify($task->startWorking(2))->true();
        verify(Response::findOne(1)->status)->equals(Response::STATUS_ACCEPTED);
        verify(Task::findOne(1)->status)->equals(TaskBasic::STATUS_WORKING);
        verify(Task::findOne(1)->executor_id)->equals(2);

        verify($response->reject())->true();
        verify(Response::findOne(1)->status)->equals(Response::STATUS_REJECTED);
    }

    public function testTaskCanBeFailedAndCancelled(): void
    {
        $working = Task::findOne(2);
        $new = Task::findOne(1);

        verify($working->failTask())->true();
        verify($new->cancelTask())->true();
        verify(Task::findOne(2)->status)->equals(TaskBasic::STATUS_FAILED);
        verify(Task::findOne(1)->status)->equals(TaskBasic::STATUS_CANCELLED);
    }

    public function testUserRatingCountersAndRankAreUpdated(): void
    {
        $executor = User::findOne(2);

        verify($executor->getUserRating())->equals('5');
        verify($executor->calcTotalScore())->equals('5');
        verify($executor->increaseCounterCompletedTasks())->true();
        verify(User::findOne(2)->succesful_tasks)->equals(1);
        verify(User::findOne(2)->total_score)->equals(5.0);
        verify(User::findOne(2)->getUserRank())->equals(1);
    }

    public function testTaskSearchFiltersByCategoryAndUserRole(): void
    {
        Yii::$app->user->login(User::findOne(2));
        $search = new TaskSearch();

        $publicTasks = $search->getTasks(1)['tasks'];
        verify(array_column($publicTasks, 'id'))->equals([1]);

        $customerTasks = $search->getUserTasks(1, User::ROLE_CUSTOMER, [
            TaskBasic::STATUS_NEW,
            TaskBasic::STATUS_WORKING,
        ])['tasks'];
        $this->assertEqualsCanonicalizing([1, 2], array_column($customerTasks, 'id'));
    }

    public function testHiddenContactsAreVisibleOnlyToOwnerOrCustomerWithCommonTask(): void
    {
        Yii::$app->user->login(User::findOne(2));
        verify(UsersHelper::userCanSeeContacts(2, true))->true();

        Yii::$app->user->logout();
        Yii::$app->user->login(User::findOne(1));
        verify(UsersHelper::userCanSeeContacts(4, true))->true();
        verify(UsersHelper::userCanSeeContacts(2, true))->false();
        verify(UsersHelper::userCanSeeContacts(2, false))->true();
    }
}

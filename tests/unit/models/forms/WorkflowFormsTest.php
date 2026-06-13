<?php

namespace tests\unit\models\forms;

use app\models\forms\EditProfileForm;
use app\models\forms\NewResponseForm;
use app\models\forms\NewReviewForm;
use app\models\forms\NewTaskForm;
use app\models\forms\SecureProfileForm;
use app\models\Response;
use app\models\Review;
use app\models\Task;
use app\models\User;
use Taskforce\Models\Task as TaskBasic;
use tests\unit\DbTestCase;
use Yii;

class WorkflowFormsTest extends DbTestCase
{
    protected function _after()
    {
        Yii::$app->user->logout();
    }

    public function testCustomerCreatesTaskWithoutLocation(): void
    {
        Yii::$app->user->login(User::findOne(1));
        $form = new NewTaskForm([
            'title' => 'Доставить важные документы',
            'description' => 'Нужно забрать документы и доставить адресату сегодня вечером.',
            'category' => 1,
            'budget' => '1500',
            'deadline' => date('Y-m-d', strtotime('+1 day')),
        ]);

        $taskId = $form->createTask();

        verify($taskId)->notEmpty();
        $task = Task::findOne($taskId);
        verify($task->customer_id)->equals(1);
        verify($task->status)->equals(TaskBasic::STATUS_NEW);
        verify($task->deadline)->stringStartsWith(date('Y-m-d', strtotime('+1 day')));
    }

    public function testTaskDeadlineMustBeValidAndInFuture(): void
    {
        $invalid = new NewTaskForm(['deadline' => '2026-99-99']);
        $invalid->validate(['deadline']);
        verify($invalid->errors)->arrayHasKey('deadline');

        $past = new NewTaskForm(['deadline' => date('Y-m-d', strtotime('-1 day'))]);
        $past->validate(['deadline']);
        verify($past->errors)->arrayHasKey('deadline');
    }

    public function testExecutorCreatesResponseAndInvalidPriceIsRejected(): void
    {
        Yii::$app->user->login(User::findOne(2));
        $valid = new NewResponseForm(['comment' => '<b>Готов</b>', 'price' => '1200']);

        verify($valid->createResponse(1))->true();
        $response = Response::find()->where(['task_id' => 1, 'executor_id' => 2])
            ->orderBy(['id' => SORT_DESC])->one();
        verify($response->comment)->equals('Готов');
        verify($response->price)->equals(1200);

        $invalid = new NewResponseForm(['price' => '0']);
        verify($invalid->createResponse(1))->false();
    }

    public function testCustomerCreatesReviewAndCompletesTask(): void
    {
        Yii::$app->user->login(User::findOne(1));
        $form = new NewReviewForm(['comment' => '<i>Всё хорошо</i>', 'grade' => '4']);

        verify($form->createReview(2, 4))->true();
        verify(Review::findOne(['task_id' => 2, 'executor_id' => 4])->comment)->equals('Всё хорошо');
        verify(Task::findOne(2)->status)->equals(TaskBasic::STATUS_COMPLETED);
    }

    public function testSecureProfileChangesPasswordAndContactVisibility(): void
    {
        Yii::$app->user->login(User::findOne(2));
        $form = new SecureProfileForm([
            'oldPassword' => 'demo',
            'newPassword' => 'new-secret',
            'repeatPassword' => 'new-secret',
            'hiddenContacts' => true,
        ]);

        verify($form->saveProfile(2))->true();
        $user = User::findOne(2);
        verify($user->validatePassword('new-secret'))->true();
        verify($user->hidden_contacts)->equals(1);
    }

    public function testEditProfilePersistsSanitizedFields(): void
    {
        $form = new EditProfileForm([
            'name' => '<b>Новое имя</b>',
            'email' => 'updated@example.com',
            'phone' => '79001234567',
            'telegram' => '@updated',
            'information' => '<script>text</script>',
            'specializations' => ['Курьер', 'Уборка'],
        ]);

        verify($form->saveProfile(2))->true();
        $user = User::findOne(2);
        verify($user->name)->equals('Новое имя');
        verify($user->information)->equals('text');
        verify($user->specializations)->equals('Курьер, Уборка');
    }
}

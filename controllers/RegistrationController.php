<?php

namespace app\controllers;

use app\models\forms\RegistrationForm;
use Yii;

/**
 * Контроллер для обработки регистрации пользователя.
 */
class RegistrationController extends NotSecuredController
{
    /**
     * Обрабатывает запрос на регистрацию нового пользователя.
     *
     * @return \yii\web\Response|string Редирект при успешной регистрации или рендер страницы.
     */
    public function actionIndex(): \yii\web\Response | string
    {
        $registration = new RegistrationForm();

        if (Yii::$app->request->getIsPost()) {
            $registration->load(Yii::$app->request->post());
            if ($registration->createUser()) {
                return $this->redirect(['tasks']);
            }
        }

        return $this->render('index', ['registration' => $registration]);
    }
}

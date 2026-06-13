<?php

namespace app\controllers;

use app\models\forms\LoginForm;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Контроллер для обработки действий на главной лендинге.
 */
class LandingController extends NotSecuredController
{
    public $layout = 'landing';

    /**
     * Метод обрабатывает страницу лендинга и выполняет авторизацию в случае, если был выполнен вход на сайт.
     *
     * @return \yii\web\Response|string|array
     */
    public function actionIndex(): \yii\web\Response|string|array
    {
        $loginForm = new LoginForm();

        if (Yii::$app->request->getIsPost()) {
            $loginForm->load(Yii::$app->request->post());

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($loginForm);
            }

            if ($loginForm->validate()) {
                $user = $loginForm->getUser();
                Yii::$app->user->login($user);

                return Yii::$app->response->redirect(['/tasks']);
            }
        }

        return $this->render('index', ['login' => $loginForm]);
    }
}

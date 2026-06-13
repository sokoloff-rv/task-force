<?php

namespace app\controllers;

use app\models\User;
use app\models\VkUser;
use Yii;
use yii\base\Exception;
use yii\web\BadRequestHttpException;

/**
 * Контроллер авторизации.
 */
class AuthController extends NotSecuredController
{
    /**
     * Метод обрабатывает запрос на авторизацию через ВКонтакте.
     *
     * @return \yii\web\Response
     * @throws BadRequestHttpException если возникает ошибка при работе с клиентом авторизации.
     */
    public function actionVk(): \yii\web\Response
    {
        try {
            $url = Yii::$app->authClientCollection->getClient("vkid")->buildAuthUrl();
            return Yii::$app->getResponse()->redirect($url);
        } catch (Exception $error) {
            throw new BadRequestHttpException("Ошибка при работе с клиентом авторизации: " . $error->getMessage());
        }
    }

    /**
     * Метод обрабатывает запрос на вход пользователя через ВКонтакте.
     *
     * @return \yii\web\Response
     * @throws BadRequestHttpException если возникает ошибка при авторизации через ВКонтакте.
     */
    public function actionLogin(): \yii\web\Response
    {
        try {
            $client = Yii::$app->authClientCollection->getClient("vkid");
            $code = Yii::$app->request->get('code');
            // VK ID возвращает device_id на redirect; он обязателен при обмене кода на токен.
            $deviceId = Yii::$app->request->get('device_id');
            $accessToken = $client->fetchAccessToken($code, ['device_id' => $deviceId]);
            $userAttributes = $client->getUserAttributes();

            $foundUser = User::findOne(['vk_id' => $userAttributes['user_id']]);
            if ($foundUser) {
                Yii::$app->user->login($foundUser);
                return Yii::$app->response->redirect(['/tasks']);
            }

            $vkUser = new VkUser();
            $vkUser->createUser($userAttributes);

            return Yii::$app->response->redirect(['/tasks']);
        } catch (Exception $error) {
            throw new BadRequestHttpException("Ошибка при авторизации через ВКонтакте: " . $error->getMessage());
        }
    }
}

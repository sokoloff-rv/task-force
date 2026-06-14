<?php

namespace app\controllers;

use app\models\User;
use app\models\VkUser;
use app\models\forms\VkEmailForm;
use Yii;
use yii\base\Exception;
use yii\web\BadRequestHttpException;

/**
 * Контроллер авторизации.
 */
class AuthController extends NotSecuredController
{
    private const VK_REGISTRATION_SESSION = 'vkRegistration';

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
            $email = $userAttributes['email'] ?? (method_exists($accessToken, 'getParam') ? $accessToken->getParam('email') : null);

            $foundUser = User::findOne(['vk_id' => $userAttributes['user_id']]);
            if (!$foundUser && $email) {
                $foundUser = User::findOne(['email' => $email]);
            }
            if ($foundUser) {
                if (!$foundUser->vk_id) {
                    $foundUser->vk_id = $userAttributes['user_id'];
                    if (!$foundUser->save()) {
                        throw new \RuntimeException('Не удалось связать аккаунт с ВКонтакте.');
                    }
                }
                Yii::$app->user->login($foundUser);
                return Yii::$app->response->redirect(['/tasks']);
            }

            if (!$email) {
                Yii::$app->session->set(self::VK_REGISTRATION_SESSION, [
                    'user_id' => $userAttributes['user_id'],
                    'first_name' => $userAttributes['first_name'] ?? '',
                    'last_name' => $userAttributes['last_name'] ?? '',
                    'birthday' => $userAttributes['birthday'] ?? null,
                    'city' => $userAttributes['city'] ?? null,
                    'avatar' => $userAttributes['avatar'] ?? null,
                ]);

                return Yii::$app->response->redirect(['/auth/email']);
            }

            $userAttributes['email'] = $email;
            $vkUser = new VkUser();
            $vkUser->createUser($userAttributes);

            return Yii::$app->response->redirect(['/tasks']);
        } catch (\Throwable $error) {
            Yii::error($error);
            throw new BadRequestHttpException('Ошибка при авторизации через ВКонтакте.');
        }
    }

    public function actionEmail(): \yii\web\Response|string
    {
        $userAttributes = Yii::$app->session->get(self::VK_REGISTRATION_SESSION);
        if (!$userAttributes) {
            return Yii::$app->response->redirect(['/landing']);
        }

        $form = new VkEmailForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $userAttributes['email'] = $form->email;
            $vkUser = new VkUser();
            $vkUser->createUser($userAttributes);
            Yii::$app->session->remove(self::VK_REGISTRATION_SESSION);

            return Yii::$app->response->redirect(['/tasks']);
        }

        return $this->render('email', ['form' => $form]);
    }
}

<?php

namespace app\components;

use yii\authclient\OAuth2;

/**
 * Клиент авторизации через VK ID.
 *
 * Реализует актуальный протокол VK ID (OAuth 2.1) на смену устаревшему
 * OAuth ВКонтакте (oauth.vk.com / api.vk.com), отключённому со стороны VK.
 * Особенности протокола:
 *  - обязательный PKCE (code_challenge / code_verifier);
 *  - параметр device_id, который VK возвращает на redirect и который
 *    необходимо передавать при обмене кода на токен;
 *  - получение профиля через эндпоинт user_info вместо метода users.get.
 *
 * @see https://id.vk.com/about/business/go/docs/ru/vkid/latest/vk-id/connection/start-integration/auth-without-sdk-web/auth-flow-web
 */
class VKID extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $authUrl = 'https://id.vk.com/authorize';
    /**
     * {@inheritdoc}
     */
    public $tokenUrl = 'https://id.vk.com/oauth2/auth';
    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = 'https://id.vk.com/oauth2';
    /**
     * {@inheritdoc}
     */
    public $enablePkce = true;
    /**
     * {@inheritdoc}
     */
    public $scope = 'email';

    /**
     * Инициализирует атрибуты пользователя из эндпоинта user_info VK ID.
     *
     * Токен доступа и идентификатор приложения подставляются в запрос
     * методом applyAccessTokenToRequest при его отправке.
     *
     * @return array Массив атрибутов пользователя (содержимое поля "user").
     */
    protected function initUserAttributes()
    {
        $response = $this->api('user_info', 'POST');

        return $response['user'] ?? $response;
    }

    /**
     * Подставляет учётные данные в запрос на получение токена.
     *
     * VK ID использует PKCE вместо client_secret, поэтому в запрос
     * передаётся только идентификатор приложения.
     *
     * @param \yii\httpclient\Request $request Экземпляр HTTP-запроса.
     */
    protected function applyClientCredentialsToRequest($request)
    {
        $request->addData([
            'client_id' => $this->clientId,
        ]);
    }

    /**
     * Подставляет токен доступа и идентификатор приложения в запрос к API.
     *
     * @param \yii\httpclient\Request $request Экземпляр HTTP-запроса.
     * @param \yii\authclient\OAuthToken|null $accessToken Токен доступа.
     */
    public function applyAccessTokenToRequest($request, $accessToken = null)
    {
        if ($accessToken === null) {
            $accessToken = $this->getAccessToken();
        }

        $request->addData([
            'client_id' => $this->clientId,
            'access_token' => $accessToken->getToken(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'vkid';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'VK ID';
    }
}

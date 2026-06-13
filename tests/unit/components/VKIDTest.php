<?php

namespace tests\unit\components;

use Codeception\Test\Unit;
use app\components\VKID;
use yii\authclient\OAuthToken;
use yii\httpclient\Request;

class VKIDTest extends Unit
{
    public function testVkIdEndpointsAreConfigured(): void
    {
        $client = new VKID();

        verify($client->authUrl)->equals('https://id.vk.com/authorize');
        verify($client->tokenUrl)->equals('https://id.vk.com/oauth2/auth');
        verify($client->apiBaseUrl)->equals('https://id.vk.com/oauth2');
    }

    public function testPkceIsEnabled(): void
    {
        $client = new VKID();

        verify($client->enablePkce)->true();
        verify($client->scope)->equals('email');
    }

    public function testClientCredentialsUsePkceWithoutSecret(): void
    {
        $client = new VKID();
        $client->clientId = 'test-client-id';
        $client->clientSecret = 'test-client-secret';

        $method = new \ReflectionMethod(VKID::class, 'applyClientCredentialsToRequest');
        $method->setAccessible(true);

        $request = new Request();
        $method->invoke($client, $request);

        $data = $request->getData();
        verify($data)->arrayHasKey('client_id');
        verify($data['client_id'])->equals('test-client-id');
        verify($data)->arrayHasNotKey('client_secret');
    }

    public function testUserAttributesAreLoadedThroughApiMethod(): void
    {
        $client = new class extends VKID {
            public array $apiCall = [];

            public function api($apiSubUrl, $method = 'GET', $data = [], $headers = [])
            {
                $this->apiCall = [$apiSubUrl, $method];
                return ['user' => ['user_id' => 42, 'email' => 'vk@example.com']];
            }

            public function loadUserAttributes(): array
            {
                return $this->initUserAttributes();
            }
        };

        verify($client->loadUserAttributes())->equals([
            'user_id' => 42,
            'email' => 'vk@example.com',
        ]);
        verify($client->apiCall)->equals(['user_info', 'POST']);
    }

    public function testAccessTokenAndClientIdAreAddedToApiRequest(): void
    {
        $client = new VKID();
        $client->clientId = 'test-client-id';
        $request = new Request();
        $token = new OAuthToken(['token' => 'access-token']);

        $client->applyAccessTokenToRequest($request, $token);

        verify($request->getData())->equals([
            'client_id' => 'test-client-id',
            'access_token' => 'access-token',
        ]);
    }
}

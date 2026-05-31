<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\City;

/**
 * Класс VkUser предназначен для работы с пользователями, аутентифицированными через ВКонтакте.
 */
class VkUser extends Model
{
    /**
     * Создает нового пользователя на основе данных, полученных из ВКонтакте.
     *
     * @param array $userData Массив с данными пользователя из ВКонтакте.
     * @return void
     */
    public function createUser($userData)
    {
        $user = new User;
        $user->name = trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? ''));
        $user->email = $userData['email'] ?? null;

        $birthdayDate = !empty($userData['birthday'])
            ? \DateTime::createFromFormat('d.m.Y', $userData['birthday'])
            : false;
        $user->birthday = $birthdayDate ? $birthdayDate->format('Y-m-d') : null;

        // Вход выполняется по VK ID, поэтому пароль задаётся случайным.
        $user->password = Yii::$app->getSecurity()->generatePasswordHash(
            Yii::$app->getSecurity()->generateRandomString()
        );

        // VK ID не возвращает город в user_info, поэтому он заполняется при наличии.
        if (!empty($userData['city']['title'])) {
            $user->city_id = City::getIdByName($userData['city']['title']);
        }

        $user->vk_id = $userData['user_id'];
        $user->avatar = $userData['avatar'] ?? null;
        $user->role = User::ROLE_CUSTOMER;
        $user->save(false);

        Yii::$app->user->login($user);
    }
}

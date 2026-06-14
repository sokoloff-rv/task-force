<?php

namespace app\models\forms;

use app\models\User;
use yii\base\Model;

class VkEmailForm extends Model
{
    public string $email = '';

    public function rules(): array
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 100],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'Этот email уже используется. Войдите в существующий аккаунт другим способом.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'email' => 'Email',
        ];
    }
}

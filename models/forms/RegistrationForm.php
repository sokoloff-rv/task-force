<?php

namespace app\models\forms;

use app\models\City;
use app\models\User;
use Yii;
use yii\base\Model;

/**
 * Класс формы регистрации нового пользователя.
 */
class RegistrationForm extends Model
{
    public string $name = '';
    public string $email = '';
    public string $city = '';
    public string $password = '';
    public string $passwordRepeat = '';
    public bool $isExecutor = false;

    /**
     * Возвращает список меток атрибутов.
     *
     * @return array Список меток атрибутов.
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Ваше имя',
            'email' => 'Email',
            'city' => 'Город',
            'password' => 'Пароль',
            'passwordRepeat' => 'Повтор пароля',
            'isExecutor' => 'Я собираюсь откликаться на задания',
        ];
    }

    /**
     * Возвращает список правил валидации для атрибутов модели.
     *
     * @return array Список правил валидации.
     */
    public function rules()
    {
        return [
            [['name', 'email', 'city', 'password', 'passwordRepeat'], 'required'],
            ['email', 'email'],
            [['email'], 'unique', 'targetClass' => User::class, 'targetAttribute' => ['email' => 'email']],
            [['city'], 'exist', 'targetClass' => City::class, 'targetAttribute' => ['city' => 'id']],
            [['passwordRepeat'], 'compare', 'compareAttribute' => 'password'],
            [['isExecutor'], 'boolean'],
            [['name', 'email', 'city', 'isExecutor'], 'filter', 'filter' => 'strip_tags'],
        ];
    }

    /**
     * Создает новый объект пользователя на основе данных формы.
     *
     * @return User Новый объект пользователя.
     */
    public function newUser()
    {
        $user = new User;
        $user->name = $this->name;
        $user->email = $this->email;
        $user->password = $this->password;
        $user->city_id = $this->city;
        $user->role = $this->isExecutor ? User::ROLE_EXECUTOR : User::ROLE_CUSTOMER;
        return $user;
    }

    /**
     * Создает и сохраняет нового пользователя на основе данных формы.
     *
     * @return bool true, если пользователь успешно создан и авторизован, иначе false.
     */
    public function createUser(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->password = Yii::$app->security->generatePasswordHash($this->password);
        $user = $this->newUser();
        if (!$user->save(false)) {
            return false;
        }

        return Yii::$app->user->login($user);
    }
}

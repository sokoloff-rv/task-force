<?php

namespace app\models;

use Taskforce\Models\Task as TaskBasic;
use Yii;
use yii\web\IdentityInterface;

/**
 * Модель для таблицы "users".
 *
 * @property int $id Идентификатор пользователя.
 * @property string $name Имя пользователя.
 * @property string $email Электронная почта пользователя.
 * @property string $password Хэшированный пароль пользователя.
 * @property string|null $birthday Дата рождения пользователя.
 * @property string|null $phone Номер телефона пользователя.
 * @property string|null $telegram Имя пользователя в Telegram.
 * @property string|null $information Информация о пользователе.
 * @property string|null $specializations Специализации пользователя.
 * @property string|null $avatar URL аватара пользователя.
 * @property string|null $register_date Дата регистрации пользователя.
 * @property string $role Роль пользователя (customer или executor).
 * @property int|null $succesful_tasks Количество успешно выполненных задач.
 * @property int|null $failed_tasks Количество проваленных задач.
 * @property int|null $city_id Идентификатор города пользователя.
 * @property int|null $vk_id Идентификатор пользователя ВКонтакте.
 * @property int $hidden_contacts Скрыть контакты для всех, кроме заказчика.
 * @property float $total_score Общий рейтинг пользователя.
 *
 * @property City $city Связь с моделью города.
 * @property Response $responses Связь с моделью откликов.
 * @property Review $CustomerReviews Связь с моделью отзывов заказчиков.
 * @property Review $ReviewsOnExecutor Связь с моделью отзывов исполнителей.
 * @property Task $CustomerTasks Связь с моделью задач заказчиков.
 * @property Task $ExecutorTasks Связь с моделью задач исполнителей.
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    const ROLE_CUSTOMER = 'customer';
    const ROLE_EXECUTOR = 'executor';

    /**
     * Возвращает название таблицы.
     *
     * @return string Название таблицы.
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * Определяет правила валидации для атрибутов модели.
     *
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            [['name', 'email', 'password', 'role'], 'required'],
            [['birthday', 'register_date'], 'safe'],
            [['information', 'role'], 'string'],
            [['succesful_tasks', 'failed_tasks', 'city_id', 'vk_id', 'hidden_contacts'], 'integer'],
            [['total_score'], 'number'],
            [['name'], 'string', 'max' => 150],
            [['email', 'password', 'phone', 'telegram'], 'string', 'max' => 100],
            [['specializations', 'avatar'], 'string', 'max' => 255],
            [['email'], 'unique'],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::class, 'targetAttribute' => ['city_id' => 'id']],
        ];
    }

    /**
     * Определяет метки атрибутов.
     *
     * @return array Метки атрибутов.
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'birthday' => 'Birthday',
            'phone' => 'Phone',
            'telegram' => 'Telegram',
            'information' => 'Information',
            'specializations' => 'Specializations',
            'avatar' => 'Avatar',
            'register_date' => 'Register Date',
            'role' => 'Role',
            'succesful_tasks' => 'Succesful Tasks',
            'failed_tasks' => 'Failed Tasks',
            'city_id' => 'City ID',
            'vk_id' => 'Vk User ID',
            'hidden_contacts' => 'Hide contacts for everyone except the customer',
            'total_score' => 'User total score'
        ];
    }

    /**
     * Получает запрос для связи с моделью города.
     *
     * @return \yii\db\ActiveQuery Объект запроса ActiveQuery.
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * Получает запрос для связи с моделью откликов.
     *
     * @return \yii\db\ActiveQuery Объект запроса ActiveQuery.
     */
    public function getResponses()
    {
        return $this->hasMany(Response::class, ['executor_id' => 'id']);
    }

    /**
     * Получает запрос для связи с моделью отзывов от заказчиков.
     *
     * @return \yii\db\ActiveQuery Объект запроса ActiveQuery.
     */
    public function getCustomerReviews()
    {
        return $this->hasMany(Review::class, ['customer_id' => 'id']);
    }

    /**
     * Получает запрос для связи с моделью отзывов на исполнителя.
     *
     * @return \yii\db\ActiveQuery Объект запроса ActiveQuery.
     */
    public function getReviewsOnExecutor()
    {
        return $this->hasMany(Review::class, ['executor_id' => 'id']);
    }

    /**
     * Получает запрос для связи с моделью задач заказчика.
     *
     * @return \yii\db\ActiveQuery Объект запроса ActiveQuery.
     */
    public function getCustomerTasks()
    {
        return $this->hasMany(Task::class, ['customer_id' => 'id']);
    }

    /**
     * Получает запрос для связи с моделью задач исполнителя.
     *
     * @return \yii\db\ActiveQuery Объект запроса ActiveQuery.
     */
    public function getExecutorTasks()
    {
        return $this->hasMany(Task::class, ['executor_id' => 'id']);
    }

    /**
     * Возвращает текущего пользователя.
     *
     * @return User Текущий пользователь.
     */
    public static function getCurrentUser(): User
    {
        return User::findOne(Yii::$app->user->getId());
    }

    /**
     * Возвращает рейтинг пользователя.
     *
     * @return string Рейтинг пользователя.
     */
    public function getUserRating(): string
    {
        $sumOfGrades = 0;
        $reviews = $this->reviewsOnExecutor;

        foreach ($reviews as $review) {
            $sumOfGrades += $review['grade'];
        }

        if ($sumOfGrades > 0) {
            $rate = round($sumOfGrades / (count($reviews) + $this->failed_tasks), 2);
        } else {
            $rate = 0;
        }

        return $rate;
    }

    /**
     * Вычисляет общий балл пользователя.
     *
     * @return string Общий балл пользователя.
     */
    public function calcTotalScore(): string
    {
        $reviews = $this->reviewsOnExecutor;
        $sumOfGrades = array_sum(array_column($reviews, 'grade'));
        $totalReviews = count($reviews);

        if ($totalReviews > 0) {
            $totalScore = round($sumOfGrades / $totalReviews, 2);
        } else {
            $totalScore = 0;
        }

        return $totalScore;
    }

    /**
     * Обновляет общий балл пользователя.
     *
     * @return bool Возвращает true, если обновление прошло успешно, иначе false.
     */
    public function updateTotalScore(): bool
    {
        $totalScore = $this->calcTotalScore();
        $this->total_score = $totalScore;
        return $this->save();
    }

    /**
     * Увеличивает счетчик выполненных задач пользователя.
     *
     * @return bool Возвращает true, если обновление прошло успешно, иначе false.
     */
    public function increaseCounterCompletedTasks(): bool
    {
        $this->succesful_tasks += 1;
        $this->updateTotalScore();
        return $this->save();
    }

    /**
     * Увеличивает счетчик проваленных задач пользователя.
     *
     * @return bool Возвращает true, если обновление прошло успешно, иначе false.
     */
    public function increaseCounterFailedTasks(): bool
    {
        $this->failed_tasks += 1;
        $this->updateTotalScore();
        return $this->save();
    }

    /**
     * Возвращает место в рейтинге на основе общего балла.
     *
     * @return int Место в рейтинге.
     */
    public function getUserRank(): int
    {
        $users = User::find()
            ->orderBy(['total_score' => SORT_DESC, 'id' => SORT_ASC])
            ->all();

        $rank = 1;
        foreach ($users as $user) {
            if ($user->id == $this->id) {
                return $rank;
            }
            $rank++;
        }

        return 0;
    }

    /**
     * Возвращает статус пользователя (занят или открыт для новых заказов).
     *
     * @return string Статус пользователя.
     */
    public function getUserStatus(): string
    {
        if (Task::findOne(['executor_id' => $this->id, 'status' => TaskBasic::STATUS_WORKING])) {
            return 'Занят';
        }
        return 'Открыт для новых заказов';
    }

    /**
     * Находит объект пользователя по идентификатору.
     *
     * @param int $id Идентификатор пользователя.
     * @return User|null Возвращает найденного пользователя или null, если пользователь не найден.
     */
    public static function findIdentity($id): ?User
    {
        return self::findOne($id);
    }

    /**
     * Находит объект пользователя по маркеру доступа (не реализовано).
     *
     * @param string $token Маркер доступа.
     * @param string|null $type Тип маркера доступа.
     * @return User|null Возвращает null, так как функционал не реализован.
     */
    public static function findIdentityByAccessToken($token, $type = null): ?User
    {
        return null;
    }

    /**
     * Возвращает идентификатор пользователя.
     *
     * @return int|null Идентификатор пользователя.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Возвращает ключ аутентификации (не реализовано).
     *
     * @return string|null Возвращает null, так как функционал не реализован.
     */
    public function getAuthKey(): ?string
    {
        return null;
    }

    /**
     * Проверяет правильность ключа аутентификации (не реализовано).
     *
     * @param string $authKey Ключ аутентификации.
     * @return bool Возвращает false, так как функционал не реализован.
     */
    public function validateAuthKey($authKey): bool
    {
        return $authKey === $this->getAuthKey();
    }

    /**
     * Проверяет правильность пароля пользователя.
     *
     * @param string $password Пароль для проверки.
     * @return bool Возвращает true, если пароль правильный, иначе false.
     */
    public function validatePassword($password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

}

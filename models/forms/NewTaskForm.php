<?php

namespace app\models\forms;

use app\models\Category;
use app\models\Task;
use app\models\File;
use app\models\City;
use Taskforce\Models\Task as TaskBasic;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use Taskforce\Utils\Geocoder;

/**
 * Класс формы создания новой задачи.
 */
class NewTaskForm extends Model
{
    public string $title = '';
    public string $description = '';
    public int $category = 0;
    public string $location = '';
    public string $budget = '';
    public string $deadline = '';
    public array $files = [];

    /**
     * Возвращает список меток атрибутов.
     *
     * @return array Список меток атрибутов.
     */
    public function attributeLabels(): array
    {
        return [
            'title' => 'Опишите суть работы',
            'description' => 'Подробности задания',
            'category' => 'Категория',
            'location' => 'Локация',
            'budget' => 'Бюджет',
            'deadline' => 'Срок исполнения',
            'files' => 'Файлы',
        ];
    }

    /**
     * Возвращает список правил валидации для атрибутов модели.
     *
     * @return array Список правил валидации.
     */
    public function rules(): array
    {
        return [
            [['title', 'description', 'category'], 'required'],
            ['title', 'string', 'min' => 10],
            ['description', 'string', 'min' => 30],
            [['category'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category' => 'id']],
            [['location'], 'app\validators\LocationValidator'],
            ['budget', 'integer', 'min' => 1],
            [['deadline'], 'date', 'format' => 'php:Y-m-d'],
            [['deadline'], 'compare', 'compareValue' => date('Y-m-d'),
                'operator' => '>', 'type' => 'date',
                'message' => 'Срок выполнения не может быть в прошлом'],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 5, 'maxSize' => 5 * 1024 * 1024,
                'extensions' => ['png', 'jpg', 'jpeg', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'],
                'checkExtensionByMimeType' => true],
            [['title', 'description', 'category', 'location', 'budget', 'deadline'], 'filter', 'filter' => 'strip_tags'],
        ];
    }

    /**
     * Создает новый объект задачи на основе данных формы.
     *
     * @return Task Новый объект задачи.
     */
    public function newTask(): Task
    {
        $task = new Task;

        $task->title = $this->title;
        $task->description = $this->description;
        $task->category_id = $this->category;

        if ($this->location) {
            $locationData = Geocoder::getLocationData($this->location);

            $task->city_id = City::findOne(['name' => $locationData['city']])->id;
            $task->location = $locationData['address'];
            $task->longitude = $locationData['coordinates'][0];
            $task->latitude = $locationData['coordinates'][1];
        }

        $task->budget = $this->budget;
        $task->deadline = $this->deadline;
        $task->status = TaskBasic::STATUS_NEW;
        $task->customer_id = Yii::$app->user->getId();
        return $task;
    }

    /**
     * Создает и сохраняет новую задачу, основанную на данных формы.
     *
     * @return int|bool Возвращает ID созданной задачи, если задача успешно создана и сохранена, иначе false.
     */
    public function createTask(): int|bool
    {
        $this->files = UploadedFile::getInstances($this, 'files');

        if ($this->validate()) {
            $newTask = $this->newTask();
            $newTask->save(false);
            foreach ($this->files as $file) {
                $newFileName = uniqid('upload') . '.' . $file->getExtension();
                $file->saveAs('@webroot/uploads/' . $newFileName);
                $fileLink = '/uploads/' . $newFileName;
                File::saveFile($fileLink, $newTask->id);
            }
            return $newTask->id;
        }

        return false;
    }
}

<?php
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Category;
use app\models\forms\TasksFilter;

$this->title = 'Taskforce - Задания';
$categoriesQuery = Category::find()->select(['alias', 'name'])->all();
$categories = ArrayHelper::map($categoriesQuery, 'alias', 'name');
?>

<div class="left-column">
<h3 class="head-main head-task">Новые задания</h3>

<?php foreach ($tasks as $task): ?>
    <div class="task-card">
        <div class="header-task">
            <a  href="#" class="link link--block link--big"><?=$task->title?></a>
            <p class="price price--task"><?=$task->budget?></p>
        </div>
        <p class="info-text">
            <!-- <span class="current-time">4 часа </span>назад -->
            <?=$task->creation_date?>
        </p>
        <p class="task-text"><?=$task->description?></p>
        <div class="footer-task">
            <p class="info-text town-text"><?=$task->city->name?></p>
            <p class="info-text category-text"><?=$task->category->name?></p>
            <a href="#" class="button button--black">Смотреть задание</a>
        </div>
    </div>
<?php endforeach;?>

<div class="pagination-wrapper">
    <ul class="pagination-list">
        <li class="pagination-item mark">
            <a href="#" class="link link--page"></a>
        </li>
        <li class="pagination-item">
            <a href="#" class="link link--page">1</a>
        </li>
        <li class="pagination-item pagination-item--active">
            <a href="#" class="link link--page">2</a>
        </li>
        <li class="pagination-item">
            <a href="#" class="link link--page">3</a>
        </li>
        <li class="pagination-item mark">
            <a href="#" class="link link--page"></a>
        </li>
    </ul>
</div>
</div>
<div class="right-column">
<div class="right-card black">
	<div class="search-form">
        <?php $form = ActiveForm::begin([
            'id' => 'filter-form',
            'method' => 'get',
            'fieldConfig' => [
                'template' => "{input}",
            ],
        ]); ?>

            <h4 class="head-card">Категории</h4>
            <?= $form->field($filter, 'categories')
            ->checkboxList(
                $categories,
                [
                    'class' => 'checkbox-wrapper',
                    'itemOptions' => [
                        'labelOptions' => [
                            'class' => 'control-label',
                        ],
                    ],
                ]
            ); ?>

            <h4 class="head-card">Дополнительно</h4>
            <?= $form->field($filter, 'distantWork')->checkbox(
                [
                    'id' => 'distant-work',
                    'labelOptions' => [
                        'class' => 'control-label'
                    ]
                ]
            ); ?>
            <?= $form->field($filter, 'noResponse')->checkbox(
                [
                    'id' => 'no-response',
                    'labelOptions' => [
                        'class' => 'control-label'
                    ]
                ]
            ); ?>

            <h4 class="head-card">Период</h4>
            <?= $form->field($filter, 'period')->dropDownList(
                TasksFilter::getPeriodsMap(),
                [
                    'id' => 'period-value'
                ]
            ); ?>

            <input type="submit" class="button button--blue" value="Искать">
        <?php ActiveForm::end();?>
   </div>
</div>
</div>
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Завершение регистрации';
?>

<main class="container container--registration">
    <div class="center-block">
        <div class="registration-form regular-form">
            <?php $activeForm = ActiveForm::begin([
                'id' => 'vk-email-form',
                'method' => 'post',
                'fieldConfig' => [
                    'template' => "{label}{input}\n{error}",
                ],
            ]); ?>
            <h3 class="head-main head-task">Завершение регистрации</h3>
            <p>Для завершения регистрации укажите свой email.</p>
            <?= $activeForm->field($form, 'email')->input('email') ?>
            <?= Html::submitButton('Завершить регистрацию', ['class' => 'button button--blue']) ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</main>

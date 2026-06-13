<?php

namespace app\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * Класс ресурсов для карты.
 */
class MapAsset extends AssetBundle
{
    public $js = [];

    /**
     * Скрипт Яндекс.Карт должен загружаться в <head>, чтобы объект ymaps
     * был доступен к моменту выполнения inline-скрипта ymaps.ready(init)
     * в разметке страницы просмотра задания.
     */
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD,
    ];

    /**
     * Подключает скрипт Яндекс.Карт с ключом API из параметров приложения.
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $apiKey = Yii::$app->params['yandexMapsApiKey'] ?? '';
        $this->js[] = "https://api-maps.yandex.ru/2.1/?apikey={$apiKey}&lang=ru_RU";
    }
}

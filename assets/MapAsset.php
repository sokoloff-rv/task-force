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

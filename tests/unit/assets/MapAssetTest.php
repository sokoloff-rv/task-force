<?php

namespace tests\unit\assets;

use Codeception\Test\Unit;
use app\assets\MapAsset;
use Yii;
use yii\web\View;

class MapAssetTest extends Unit
{
    public function testScriptIsRegisteredInHead(): void
    {
        $asset = new MapAsset();

        verify($asset->jsOptions)->arrayHasKey('position');
        verify($asset->jsOptions['position'])->equals(View::POS_HEAD);
    }

    public function testYandexMapsScriptIsAdded(): void
    {
        Yii::$app->params['yandexMapsApiKey'] = 'test-map-key';
        $asset = new MapAsset();

        verify($asset->js)->notEmpty();
        verify($asset->js[0])->equals(
            'https://api-maps.yandex.ru/2.1/?apikey=test-map-key&lang=ru_RU'
        );
    }
}

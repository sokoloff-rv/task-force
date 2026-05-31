<?php

namespace Taskforce\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yii;

class Geocoder
{
    /**
     * Получение данных о местоположении по заданному адресу или координатам.
     *
     * @param string $location Адрес для определения координат или координаты для определения адреса.
     * @param string $format Формат данных, который необходимо вернуть. Возможные значения:
     *  - 'coordinates' - координаты в формате [долгота, широта];
     *  - 'city' - название города;
     *  - 'address' - адрес объекта;
     *  - 'allData' - все данные в формате [координаты, название города, адрес объекта].
     * @return null|string|array Массив или строка с данными в зависимости от заданного формата, null, если невозможно определить локацию.
     * @throws \RuntimeException Если произошла ошибка при запросе к API или при парсинге ответа от API.
     * @throws \InvalidArgumentException Если задан недопустимый формат данных.
     */
    public static function getLocationData(string $location, string $format = 'allData'): null | string | array
    {
        $cache = Yii::$app->cache;
        $cacheKey = "geocode_{$location}_{$format}";

        $locationData = $cache->get($cacheKey);
        if ($locationData === false) {
            $apiKey = Yii::$app->params['yandexGeocoderApiKey'] ?? '';

            $client = new Client([
                'base_uri' => 'https://geocode-maps.yandex.ru/',
            ]);

            try {
                $response = $client->request('GET', '1.x', [
                    'query' => ['geocode' => $location, 'apikey' => $apiKey, 'format' => 'json'],
                ]);
            } catch (GuzzleException $error) {
                throw new \RuntimeException('Ошибка при запросе к API: ' . $error->getMessage());
            }

            $content = $response->getBody()->getContents();
            $responseData = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Ошибка при парсинге ответа от API: ' . json_last_error_msg());
            }

            $geoObject = $responseData['response']['GeoObjectCollection']['featureMember']['0']['GeoObject'];

            $coordinates = explode(' ', $geoObject['Point']['pos']);
            $city = self::getCityName($geoObject['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['AdministrativeArea']);
            $address = $geoObject['name'];

            $locationData = match($format) {
                'coordinates' => $coordinates,
                'city' => $city,
                'address' => $address,
                'allData' => ['coordinates' => $coordinates, 'city' => $city, 'address' => $address],
                default => throw new \InvalidArgumentException('Недопустимый формат данных'),
            };

            $cache->set($cacheKey, $locationData, 86400);
        }

        return $locationData;
    }

    /**
     * Рекурсивно ищет значение ключа 'LocalityName' в массиве и возвращает его. Если не находит, то возвращает значение ключа 'AdministrativeAreaName'.
     *
     * @param array $array Массив, в котором производится поиск.
     *
     * @return string|null Значение искомого ключа, или null, если ключ не найден.
     */
    public static function getCityName(array $array): ?string
    {
        foreach ($array as $key => $value) {
            if ($key === 'LocalityName') {
                return $value;
            }
            if (is_array($value)) {
                $result = self::getCityName($value);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return $array['AdministrativeAreaName'] ?? null;
    }
}

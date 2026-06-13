<?php

namespace tests\unit\src\Utils;

use Codeception\Test\Unit;
use Taskforce\Exceptions\ExceptionFileFormat;
use Taskforce\Exceptions\ExceptionSourceFile;
use Taskforce\Utils\ConverterCSV;
use Taskforce\Utils\Geocoder;

class UtilsTest extends Unit
{
    public function testGeocoderFindsNestedCityAndFallsBackToAdministrativeArea(): void
    {
        verify(Geocoder::getCityName([
            'AdministrativeAreaName' => 'Область',
            'Locality' => ['LocalityName' => 'Кемерово'],
        ]))->equals('Кемерово');
        verify(Geocoder::getCityName([
            'AdministrativeAreaName' => 'Московская область',
        ]))->equals('Московская область');
    }

    public function testConverterRejectsInvalidColumns(): void
    {
        $this->expectException(ExceptionFileFormat::class);
        (new ConverterCSV('/missing.csv', []))->generateSqlFile('items');
    }

    public function testConverterRejectsMissingSourceFile(): void
    {
        $this->expectException(ExceptionSourceFile::class);
        (new ConverterCSV('/definitely/missing.csv', ['name']))->generateSqlFile('items');
    }
}

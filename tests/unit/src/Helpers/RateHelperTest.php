<?php

namespace tests\unit\src\Helpers;

use Codeception\Test\Unit;
use Taskforce\Helpers\RateHelper;

class RateHelperTest extends Unit
{
    private function countFilled(string $html): int
    {
        return substr_count($html, 'fill-star');
    }

    private function countTotal(string $html): int
    {
        return substr_count($html, '<span');
    }

    public function testZeroRatingHasNoFilledStars(): void
    {
        $html = RateHelper::getStars(0);

        verify($this->countFilled($html))->equals(0);
        verify($this->countTotal($html))->equals(5);
    }

    public function testFullRatingHasFiveFilledStars(): void
    {
        $html = RateHelper::getStars(5);

        verify($this->countFilled($html))->equals(5);
        verify($this->countTotal($html))->equals(5);
    }

    public function testRatingIsRounded(): void
    {
        verify($this->countFilled(RateHelper::getStars(3.4)))->equals(3);
        verify($this->countFilled(RateHelper::getStars(3.6)))->equals(4);

        verify($this->countTotal(RateHelper::getStars(3.4)))->equals(5);
        verify($this->countTotal(RateHelper::getStars(3.6)))->equals(5);
    }
}

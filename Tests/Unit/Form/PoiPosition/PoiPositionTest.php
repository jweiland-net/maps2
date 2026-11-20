<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Form\PoiPosition;

use JWeiland\Maps2\Form\PoiPosition\PoiPosition;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PoiPositionTest extends UnitTestCase
{
    public static function invalidFormValueDataProvider(): array
    {
        return [
            'null' => [null],
            'string' => ['48.1,9.2'],
            'empty array' => [[]],
            'missing longitude' => [['latitude' => '48.1']],
            'empty coordinates' => [['latitude' => '', 'longitude' => '']],
            'non-numeric latitude' => [['latitude' => 'north', 'longitude' => '9.2']],
            'array as coordinate' => [['latitude' => ['48.1'], 'longitude' => '9.2']],
        ];
    }

    #[Test]
    #[DataProvider('invalidFormValueDataProvider')]
    public function fromFormValueWithInvalidValueReturnsNull(mixed $value): void
    {
        self::assertNull(PoiPosition::fromFormValue($value));
    }

    #[Test]
    public function fromFormValueWithNumericStringsReturnsPosition(): void
    {
        $position = PoiPosition::fromFormValue(['latitude' => '48.123456', 'longitude' => '-9.5']);

        self::assertSame(48.123456, $position?->latitude);
        self::assertSame(-9.5, $position?->longitude);
    }

    public static function emptyFormValueDataProvider(): array
    {
        return [
            'null' => [null, true],
            'empty string' => ['', true],
            'empty array' => [[], true],
            'empty coordinates' => [['latitude' => '', 'longitude' => ' '], true],
            'missing coordinates' => [['foo' => 'bar'], true],
            'latitude only' => [['latitude' => '48.1', 'longitude' => ''], false],
            'string' => ['48.1', false],
            'position' => [['latitude' => '48.1', 'longitude' => '9.2'], false],
        ];
    }

    #[Test]
    #[DataProvider('emptyFormValueDataProvider')]
    public function isEmptyFormValueDetectsEmptyValues(mixed $value, bool $expected): void
    {
        self::assertSame($expected, PoiPosition::isEmptyFormValue($value));
    }

    public static function rangeDataProvider(): array
    {
        return [
            'valid' => [48.1, 9.2, true, true],
            'valid limits' => [-90.0, 180.0, true, true],
            'latitude too high' => [90.1, 9.2, false, true],
            'latitude too low' => [-90.1, 9.2, false, true],
            'longitude too high' => [48.1, 180.1, true, false],
            'longitude too low' => [48.1, -180.1, true, false],
        ];
    }

    #[Test]
    #[DataProvider('rangeDataProvider')]
    public function rangesOfCoordinatesAreChecked(
        float $latitude,
        float $longitude,
        bool $isLatitudeValid,
        bool $isLongitudeValid,
    ): void {
        $position = new PoiPosition($latitude, $longitude);

        self::assertSame($isLatitudeValid, $position->hasValidLatitude());
        self::assertSame($isLongitudeValid, $position->hasValidLongitude());
        self::assertSame($isLatitudeValid && $isLongitudeValid, $position->isValid());
    }

    #[Test]
    public function toFormValueReturnsCoordinatesWithSixDecimals(): void
    {
        self::assertSame(
            ['latitude' => '48.100000', 'longitude' => '-9.123457'],
            (new PoiPosition(48.1, -9.1234567))->toFormValue(),
        );
    }
}

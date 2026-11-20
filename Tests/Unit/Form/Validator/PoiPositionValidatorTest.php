<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Form\Validator;

use JWeiland\Maps2\Form\Validator\PoiPositionValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PoiPositionValidatorTest extends UnitTestCase
{
    private PoiPositionValidator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Returns the translation key instead of the translated message
        $this->subject = new class () extends PoiPositionValidator {
            protected function translateErrorMessage(
                string $translateKey,
                string $extensionName = '',
                array $arguments = [],
            ): string {
                return $translateKey;
            }
        };
    }

    public static function validValueDataProvider(): array
    {
        return [
            'empty value' => [null],
            'empty string' => [''],
            'position' => [['latitude' => '48.123456', 'longitude' => '9.123456']],
            'negative position' => [['latitude' => '-33.8', 'longitude' => '-151.2']],
            'limits' => [['latitude' => '90', 'longitude' => '-180']],
            'floats' => [['latitude' => 48.1, 'longitude' => 9.2]],
        ];
    }

    #[Test]
    #[DataProvider('validValueDataProvider')]
    public function validateWithValidValueHasNoErrors(mixed $value): void
    {
        self::assertFalse($this->subject->validate($value)->hasErrors());
    }

    public static function invalidValueDataProvider(): array
    {
        return [
            'string' => ['48.1,9.2', [PoiPositionValidator::ERROR_CODE_NOT_NUMERIC]],
            'empty coordinates' => [['latitude' => '', 'longitude' => ''], [PoiPositionValidator::ERROR_CODE_NOT_NUMERIC]],
            'non-numeric' => [['latitude' => 'abc', 'longitude' => '9.2'], [PoiPositionValidator::ERROR_CODE_NOT_NUMERIC]],
            'latitude out of range' => [
                ['latitude' => '90.5', 'longitude' => '9.2'],
                [PoiPositionValidator::ERROR_CODE_LATITUDE_OUT_OF_RANGE],
            ],
            'longitude out of range' => [
                ['latitude' => '48.1', 'longitude' => '-180.5'],
                [PoiPositionValidator::ERROR_CODE_LONGITUDE_OUT_OF_RANGE],
            ],
            'both out of range' => [
                ['latitude' => '-91', 'longitude' => '181'],
                [PoiPositionValidator::ERROR_CODE_LATITUDE_OUT_OF_RANGE, PoiPositionValidator::ERROR_CODE_LONGITUDE_OUT_OF_RANGE],
            ],
        ];
    }

    /**
     * @param int[] $expectedErrorCodes
     */
    #[Test]
    #[DataProvider('invalidValueDataProvider')]
    public function validateWithInvalidValueAddsErrors(mixed $value, array $expectedErrorCodes): void
    {
        $errorCodes = array_map(
            static fn(Error $error): int => $error->getCode(),
            $this->subject->validate($value)->getErrors(),
        );

        self::assertSame($expectedErrorCodes, $errorCodes);
    }

    #[Test]
    public function validateAddsTranslatedMessage(): void
    {
        $errors = $this->subject->validate(['latitude' => '91', 'longitude' => '9'])->getErrors();

        self::assertSame(
            'validator.poiPosition.latitudeOutOfRange',
            $errors[0]->getMessage(),
        );
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Form\Validator;

use JWeiland\Maps2\Form\PoiPosition\PoiPosition;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validates the value of the form element "Maps2PoiPosition": an array with
 * numeric "latitude" (-90..90) and "longitude" (-180..180).
 * Empty values are accepted. Use the NotEmpty validator to require a position.
 *
 * The error messages are translated with the language file of maps2. Use the
 * translation files of EXT:form (by error code) to override them.
 */
class PoiPositionValidator extends AbstractValidator
{
    public const ERROR_CODE_NOT_NUMERIC = 1763380001;

    public const ERROR_CODE_LATITUDE_OUT_OF_RANGE = 1763380002;

    public const ERROR_CODE_LONGITUDE_OUT_OF_RANGE = 1763380003;

    protected function isValid(mixed $value): void
    {
        $position = PoiPosition::fromFormValue($value);
        if (!$position instanceof PoiPosition) {
            $this->addTranslatedError('validator.poiPosition.notNumeric', self::ERROR_CODE_NOT_NUMERIC);
            return;
        }

        if (!$position->hasValidLatitude()) {
            $this->addTranslatedError(
                'validator.poiPosition.latitudeOutOfRange',
                self::ERROR_CODE_LATITUDE_OUT_OF_RANGE,
            );
        }

        if (!$position->hasValidLongitude()) {
            $this->addTranslatedError(
                'validator.poiPosition.longitudeOutOfRange',
                self::ERROR_CODE_LONGITUDE_OUT_OF_RANGE,
            );
        }
    }

    private function addTranslatedError(string $key, int $code): void
    {
        $this->addError(
            $this->translateErrorMessage($key, 'maps2'),
            $code,
        );
    }
}

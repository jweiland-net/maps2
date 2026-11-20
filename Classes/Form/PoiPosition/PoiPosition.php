<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Form\PoiPosition;

/**
 * Position of the POI marker of the form element "Maps2PoiPosition".
 *
 * The value of the form element is an array with the keys "latitude" and
 * "longitude". Both values are numeric strings.
 */
final readonly class PoiPosition
{
    public const KEY_LATITUDE = 'latitude';

    public const KEY_LONGITUDE = 'longitude';

    private const MAX_LATITUDE = 90.0;

    private const MAX_LONGITUDE = 180.0;

    private const DECIMALS = 6;

    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    /**
     * Returns null, if the value is not an array with numeric coordinates
     */
    public static function fromFormValue(mixed $value): ?self
    {
        if (!is_array($value)
            || !is_numeric($value[self::KEY_LATITUDE] ?? null)
            || !is_numeric($value[self::KEY_LONGITUDE] ?? null)
        ) {
            return null;
        }

        return new self((float)$value[self::KEY_LATITUDE], (float)$value[self::KEY_LONGITUDE]);
    }

    /**
     * Both coordinates are empty, if the user has not placed the marker yet
     */
    public static function isEmptyFormValue(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === []) {
            return true;
        }

        return is_array($value)
            && self::isEmptyCoordinate($value[self::KEY_LATITUDE] ?? null)
            && self::isEmptyCoordinate($value[self::KEY_LONGITUDE] ?? null);
    }

    public function hasValidLatitude(): bool
    {
        return abs($this->latitude) <= self::MAX_LATITUDE;
    }

    public function hasValidLongitude(): bool
    {
        return abs($this->longitude) <= self::MAX_LONGITUDE;
    }

    public function isValid(): bool
    {
        return $this->hasValidLatitude() && $this->hasValidLongitude();
    }

    /**
     * @return array{latitude: string, longitude: string}
     */
    public function toFormValue(): array
    {
        return [
            self::KEY_LATITUDE => number_format($this->latitude, self::DECIMALS, '.', ''),
            self::KEY_LONGITUDE => number_format($this->longitude, self::DECIMALS, '.', ''),
        ];
    }

    private static function isEmptyCoordinate(mixed $coordinate): bool
    {
        return $coordinate === null || (is_string($coordinate) && trim($coordinate) === '');
    }
}

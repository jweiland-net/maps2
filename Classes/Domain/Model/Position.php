<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Domain\Model;

/**
 * Domain Model for Position
 * Needed by Google Maps and OpenStreetMap for lat/lng
 */
class Position
{
    /**
     * @var string
     */
    protected $formattedAddress = '';

    /**
     * @var float
     */
    protected $latitude = 0.0;

    /**
     * @var float
     */
    protected $longitude = 0.0;

    public function getFormattedAddress(): string
    {
        return $this->formattedAddress;
    }

    public function setFormattedAddress(string $formattedAddress)
    {
        $this->formattedAddress = $formattedAddress;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude)
    {
        $this->longitude = $longitude;
    }
}

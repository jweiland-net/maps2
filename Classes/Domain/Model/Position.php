<?php
namespace JWeiland\Maps2\Domain\Model;

/*
 * This file is part of the maps2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
c */
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

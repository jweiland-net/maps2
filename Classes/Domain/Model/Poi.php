<?php
declare(strict_types = 1);
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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Domain Model for Poi
 * This model is part of PoiCollection and was needed, if you work with Markers of type: Route or Area
 */
class Poi extends AbstractEntity
{
    /**
     * @var int
     */
    protected $cruserId = 0;

    /**
     * @var int
     */
    protected $posIndex = 0;

    /**
     * @var float
     */
    protected $latitude = 0.0;

    /**
     * @var float
     */
    protected $longitude = 0.0;

    public function getCruserId(): int
    {
        return $this->cruserId;
    }

    public function setCruserId(int $cruserId)
    {
        $this->cruserId = $cruserId;
    }

    public function getPosIndex(): int
    {
        return $this->posIndex;
    }

    public function setPosIndex(int $posIndex)
    {
        $this->posIndex = $posIndex;
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

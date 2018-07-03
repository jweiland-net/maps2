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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Domain Model for Poi
 * This model is part of PoiCollection and was needed, if you work with Markers of type: Route or Area
 */
class Poi extends AbstractEntity
{
    /**
     * cruser_id
     *
     * @var int
     */
    protected $cruserId = 0;

    /**
     * Position Index
     *
     * @var int
     */
    protected $posIndex = 0;

    /**
     * Latitude
     *
     * @var float
     */
    protected $latitude = 0.0;

    /**
     * Longitude
     *
     * @var float
     */
    protected $longitude = 0.0;

    /**
     * Returns the cruserId
     *
     * @return float $cruserId
     */
    public function getCruserId()
    {
        return $this->cruserId;
    }

    /**
     * Sets the cruserId
     *
     * @param int $cruserId
     * @return void
     */
    public function setCruserId($cruserId)
    {
        $this->cruserId = (int)$cruserId;
    }

    /**
     * Returns the pos_index
     *
     * @return int $posIndex
     */
    public function getPosIndex()
    {
        return $this->posIndex;
    }

    /**
     * Sets the pos_index
     *
     * @param int $posIndex
     * @return void
     */
    public function setPosIndex($posIndex)
    {
        $this->posIndex = (int)$posIndex;
    }

    /**
     * Returns the latitude
     *
     * @return float $latitude
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Sets the latitude
     *
     * @param float $latitude
     * @return void
     */
    public function setLatitude($latitude)
    {
        $this->latitude = (float)$latitude;
    }

    /**
     * Returns the longitude
     *
     * @return float $longitude
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Sets the longitude
     *
     * @param float $longitude
     * @return void
     */
    public function setLongitude($longitude)
    {
        $this->longitude = (float)$longitude;
    }
}

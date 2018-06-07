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
 * Class Location
 *
 * @category Domain/Model
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class Location
{
    /**
     * latitude
     *
     * @var float
     */
    protected $lat = 0.0;

    /**
     * longitude
     *
     * @var float
     */
    protected $lng = 0.0;

    /**
     * Setter for lat
     *
     * @param float $lat
     */
    public function setLat($lat)
    {
        $this->lat = (float)$lat;
    }

    /**
     * Getter for lat
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Setter for lng
     *
     * @param float $lng
     */
    public function setLng($lng)
    {
        $this->lng = (float)$lng;
    }

    /**
     * Getter for lng
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Getter for latitude/lat
     * Wrapper for getLat()
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->lat;
    }

    /**
     * Getter for longitude/lng
     * Wrapper for getLng()
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->lng;
    }
}

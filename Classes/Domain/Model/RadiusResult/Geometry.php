<?php
namespace JWeiland\Maps2\Domain\Model\RadiusResult;

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

use JWeiland\Maps2\Domain\Model\Location;
use JWeiland\Maps2\Domain\Model\Position;

/**
 * Class Geometry
 *
 * @category Domain/Model
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class Geometry
{
    /**
     * bounds
     *
     * @var \JWeiland\Maps2\Domain\Model\Position
     */
    protected $bounds;

    /**
     * location
     *
     * @var \JWeiland\Maps2\Domain\Model\Location
     */
    protected $location;

    /**
     * locationType
     *
     * @var string
     */
    protected $locationType;

    /**
     * viewport
     *
     * @var \JWeiland\Maps2\Domain\Model\Position
     */
    protected $viewport;

    /**
     * Setter for bounds
     *
     * @param Position $bounds
     */
    public function setBounds(Position $bounds)
    {
        $this->bounds = $bounds;
    }

    /**
     * Getter for bounds
     *
     * @return Position
     */
    public function getBounds()
    {
        return $this->bounds;
    }

    /**
     * Setter for location
     *
     * @param Location $location
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;
    }

    /**
     * Getter for location
     *
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Setter for locationType
     *
     * @param string $locationType
     */
    public function setLocationType($locationType)
    {
        $this->locationType = $locationType;
    }

    /**
     * Getter for locationType
     *
     * @return string
     */
    public function getLocationType()
    {
        return $this->locationType;
    }

    /**
     * Setter for viewport
     *
     * @param Position $viewport
     */
    public function setViewport(Position $viewport)
    {
        $this->viewport = $viewport;
    }

    /**
     * Getter for viewport
     *
     * @return Position
     */
    public function getViewport()
    {
        return $this->viewport;
    }
}

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
 * Domain Model for Position
 * This class is not part of a local DB table. It's part of the result of a Google Request
 */
class Position
{
    /**
     * northeast
     *
     * @var \JWeiland\Maps2\Domain\Model\Location
     */
    protected $northeast;

    /**
     * southwest
     *
     * @var \JWeiland\Maps2\Domain\Model\Location
     */
    protected $southwest;

    /**
     * Setter for northeast
     *
     * @param Location $northeast
     */
    public function setNortheast(Location $northeast)
    {
        $this->northeast = $northeast;
    }

    /**
     * Getter for northeast
     *
     * @return Location
     */
    public function getNortheast()
    {
        return $this->northeast;
    }

    /**
     * Setter for southwest
     *
     * @param Location $southwest
     */
    public function setSouthwest(Location $southwest)
    {
        $this->southwest = $southwest;
    }

    /**
     * Getter for southwest
     *
     * @return Location
     */
    public function getSouthwest()
    {
        return $this->southwest;
    }
}

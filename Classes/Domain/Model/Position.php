<?php
namespace JWeiland\Maps2\Domain\Model;

/**
 * This file is part of the TYPO3 CMS project.
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
 * Class Position
 *
 * @category Domain/Model
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
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
     * @var Location
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

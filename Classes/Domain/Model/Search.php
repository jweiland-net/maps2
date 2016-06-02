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
 * Class Search
 *
 * @category Domain/Model
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class Search
{

    /**
     * address
     *
     * @var string
     */
    protected $address;

    /**
     * radius
     *
     * @var int
     */
    protected $radius;

    /**
     * Returns the address
     *
     * @return string $address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the address
     *
     * @param string $address
     * @return void
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Returns the radius
     *
     * @return int $radius
     */
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * Sets the radius
     *
     * @param int $radius
     * @return void
     */
    public function setRadius($radius)
    {
        $this->radius = (int)$radius;
    }
}

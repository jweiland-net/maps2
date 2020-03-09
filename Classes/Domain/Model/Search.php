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

/**
 * Domain Model for Search queries
 * This class is not part of a local DB table
 */
class Search
{
    /**
     * address
     *
     * @var string
     */
    protected $address = '';

    /**
     * radius
     *
     * @var int
     */
    protected $radius = 50;

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
     */
    public function setAddress($address)
    {
        $this->address = (string)$address;
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
     */
    public function setRadius($radius)
    {
        $this->radius = (int)$radius;
    }
}

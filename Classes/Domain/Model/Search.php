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
     * @var string
     */
    protected $address = '';

    /**
     * @var int
     */
    protected $radius = 50;

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function getRadius(): int
    {
        return $this->radius;
    }

    public function setRadius(int $radius)
    {
        $this->radius = $radius;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Domain\Model;

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

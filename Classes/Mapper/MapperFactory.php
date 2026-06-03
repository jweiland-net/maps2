<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Mapper;

use JWeiland\Maps2\Configuration\MapProviderEnum;

/**
 * This factory returns either a Mapper for Google Maps or OpenStreetMap
 */
class MapperFactory
{
    public function __construct(
        protected iterable $mapper,
    ) {}

    public function create(MapProviderEnum $mapProvider): ?MapperInterface
    {
        foreach ($this->mapper as $mapper) {
            if ($mapper->canProcess($mapProvider)) {
                return $mapper;
            }
        }

        return null;
    }
}

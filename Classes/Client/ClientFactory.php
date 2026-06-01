<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client;

use JWeiland\Maps2\Configuration\MapProviderEnum;

/**
 * This factory creates a client for either Google Maps or OpenStreetMap
 */
final readonly class ClientFactory
{
    public function __construct(
        private iterable $mapProviderClients,
    ) {}

    public function create(MapProviderEnum $mapProvider): ?ClientInterface
    {
        foreach ($this->mapProviderClients as $mapProviderClient) {
            if ($mapProviderClient->canProcess($mapProvider)) {
                return $mapProviderClient;
            }
        }

        return null;
    }
}

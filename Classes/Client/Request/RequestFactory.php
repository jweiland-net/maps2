<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client\Request;

use JWeiland\Maps2\Configuration\MapProviderEnum;

/**
 * This factory builds new request objects for either Google Maps or OpenStreetMap.
 * This class only works as long as you keep filenames in Google Maps and OpenStreetMap folder in sync.
 */
final readonly class RequestFactory
{
    public function __construct(
        private iterable $mapProviderGeoCodingRequests,
    ) {}

    public function create(MapProviderEnum $mapProvider): ?RequestInterface
    {
        foreach ($this->mapProviderGeoCodingRequests as $mapProviderRequest) {
            if ($mapProviderRequest->canProcess($mapProvider)) {
                return $mapProviderRequest;
            }
        }

        return null;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client;

use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This factory creates a client for either Google Maps or Open Street Map
 */
class ClientFactory
{
    /**
     * @var ClientInterface[]
     */
    protected $mapping = [
        'gm' => GoogleMapsClient::class,
        'osm' => OpenStreetMapClient::class
    ];

    public function create(): ClientInterface
    {
        $mapService = GeneralUtility::makeInstance(MapService::class);

        /** @var ClientInterface $client */
        $client = GeneralUtility::makeInstance(
            $this->mapping[$mapService->getMapProvider()]
        );

        return $client;
    }
}

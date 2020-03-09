<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Client;

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

use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This factory creates a client for either Google Maps or Open Street Map
 */
class ClientFactory
{
    /**
     * @var array
     */
    protected $mapping = [
        'gm' => GoogleMapsClient::class,
        'osm' => OpenStreetMapClient::class
    ];

    /**
     * @return ClientInterface
     */
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

<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Mapper;

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
 * This factory returns either a Mapper for Google Maps or Open Street Map
 */
class MapperFactory
{
    /**
     * @var array
     */
    protected $mapping = [
        'gm' => GoogleMapsMapper::class,
        'osm' => OpenStreetMapMapper::class
    ];

    public function create(): MapperInterface
    {
        $mapService = GeneralUtility::makeInstance(MapService::class);

        /** @var MapperInterface $client */
        $client = GeneralUtility::makeInstance(
            $this->mapping[$mapService->getMapProvider()]
        );

        return $client;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Mapper;

use JWeiland\Maps2\Helper\MapHelper;
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

    /**
     * @var MapHelper
     */
    protected $mapHelper;

    public function __construct(MapHelper $mapHelper)
    {
        $this->mapHelper = $mapHelper;
    }

    public function create(): MapperInterface
    {
        /** @var MapperInterface $client */
        $client = GeneralUtility::makeInstance(
            $this->mapping[$this->mapHelper->getMapProvider()]
        );

        return $client;
    }
}

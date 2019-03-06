<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Form\Resolver;

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

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Form\Element\GoogleMapsElement;
use JWeiland\Maps2\Form\Element\OpenStreetMapElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\NodeResolverInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This resolver will decide to show either map rendered with Google Maps or Open Street Map
 */
class MapProviderResolver implements NodeResolverInterface
{
    /**
     * Global options from NodeFactory
     *
     * @var array
     */
    protected $data = [];

    /**
     * Default constructor receives full data array
     *
     * @param NodeFactory $nodeFactory
     * @param array $data
     */
    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns either a map based on Google Maps or Open Street Map
     *
     * @return string|null New class name or void if this resolver does not change current class name.
     */
    public function resolve()
    {
        $collectionType = $this->getCollectionType($this->data['databaseRow']);

        // Currently Open Street Map is only valid for collection_type Point
        if ($collectionType === 'Point') {
            $mapProvider = $this->getMapProvider($this->data['databaseRow']);
            if ($mapProvider === 'osm') {
                return OpenStreetMapElement::class;
            }
        }

        // In all other cases render map with Google Maps
        return GoogleMapsElement::class;
    }

    /**
     * @param array $databaseRow
     * @return string
     */
    protected function getCollectionType(array $databaseRow): string
    {
        if (is_array($databaseRow['collection_type'])) {
            $collectionType = current($databaseRow['collection_type']);
        } else {
            $collectionType = 'Point';
        }
        return $collectionType;
    }

    /**
     * @param array $databaseRow
     * @return string
     */
    protected function getMapProvider(array $databaseRow): string
    {
        if (is_array($databaseRow['map_provider'])) {
            $mapProvider = current($databaseRow['map_provider']);
        } else {
            $extConf = GeneralUtility::makeInstance(ExtConf::class);
            if ($extConf->getMapProvider() === 'both') {
                $mapProvider = $extConf->getDefaultMapProvider();
            } else {
                $mapProvider = $extConf->getMapProvider();
            }
        }
        return $mapProvider;

    }
}

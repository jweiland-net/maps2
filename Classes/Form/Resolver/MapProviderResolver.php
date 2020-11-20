<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Form\Resolver;

use JWeiland\Maps2\Form\Element\GoogleMapsElement;
use JWeiland\Maps2\Form\Element\OpenStreetMapElement;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\NodeResolverInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This resolver decides with which map provider the map should be rendered. Either Google Maps or Open Street Map.
 */
class MapProviderResolver implements NodeResolverInterface
{
    /**
     * Global options from NodeFactory
     *
     * @var array
     */
    protected $data = [];

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
        $mapService = GeneralUtility::makeInstance(MapService::class);
        if ($mapService->getMapProvider($this->data['databaseRow']) === 'osm') {
            return OpenStreetMapElement::class;
        } else {
            return GoogleMapsElement::class;
        }
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
}

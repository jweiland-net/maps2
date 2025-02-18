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
use JWeiland\Maps2\Helper\MapHelper;
use TYPO3\CMS\Backend\Form\NodeResolverInterface;

/**
 * This resolver decides with which map provider the map should be rendered. Either Google Maps or Open Street Map.
 */
class MapProviderResolver implements NodeResolverInterface
{
    protected array $data;

    public function __construct(protected MapHelper $mapHelper) {}

    /**
     * Retrieve the current data array from NodeFactory.
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Returns either a map based on Google Maps or Open Street Map
     *
     * @return string New class name
     */
    public function resolve(): string
    {
        if ($this->mapHelper->getMapProvider($this->data['databaseRow']) === 'osm') {
            return OpenStreetMapElement::class;
        }

        return GoogleMapsElement::class;
    }
}

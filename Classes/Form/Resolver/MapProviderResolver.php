<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Form\Resolver;

use JWeiland\Maps2\Configuration\MapProviderEnum;
use TYPO3\CMS\Backend\Form\NodeResolverInterface;

/**
 * This resolver decides with which map provider the map should be rendered. Either Google Maps or Open Street Map.
 */
final readonly class MapProviderResolver implements NodeResolverInterface
{
    public function __construct(
        private iterable $mapProviderFormElements,
        private MapProviderEnum $mapProvider,
    ) {}

    /**
     * Retrieve the current data array from NodeFactory.
     */
    public function setData(array $data): void {}

    /**
     * Returns either a map based on Google Maps or OpenStreetMap
     *
     * @return string New class name
     */
    public function resolve(): string
    {
        foreach ($this->mapProviderFormElements as $mapProviderFormElement) {
            if ($mapProviderFormElement->canProcess($this->mapProvider)) {
                return $mapProviderFormElement::class;
            }
        }

        return '';
    }
}

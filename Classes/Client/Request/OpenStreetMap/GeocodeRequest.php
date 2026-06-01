<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client\Request\OpenStreetMap;

use JWeiland\Maps2\Client\Request\RequestInterface;
use JWeiland\Maps2\Configuration\ExtConf;

/**
 * A Request class for OpenStreetMap Geocode API
 */
final readonly class GeocodeRequest implements RequestInterface
{
    public function __construct(
        protected ExtConf $extConf,
    ) {}

    /**
     * Get URI for Geocode
     *
     * @throws \Exception
     */
    public function getUri(string $rawUrlEncodedAddress): string
    {
        $uri = $this->extConf->getOpenStreetMapGeocodeUri();

        if ($uri === '') {
            return '';
        }

        if ($rawUrlEncodedAddress === '') {
            return $uri;
        }

        return sprintf($uri, $rawUrlEncodedAddress);
    }
}

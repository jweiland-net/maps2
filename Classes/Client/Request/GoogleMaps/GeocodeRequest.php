<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client\Request\GoogleMaps;

use JWeiland\Maps2\Client\Request\RequestInterface;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Configuration\MapProviderEnum;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * A Request class for Google Maps Geocode API
 */
#[AutoconfigureTag(
    name: 'maps2.request.geocoding',
)]
final readonly class GeocodeRequest implements RequestInterface
{
    public function __construct(
        protected ExtConf $extConf,
    ) {}

    public function canProcess(MapProviderEnum $mapProvider): bool
    {
        return $mapProvider === MapProviderEnum::GOOGLE_MAPS;
    }

    /**
     * Get URI for Geocode
     *
     * @throws \Exception
     */
    public function getUri(string $rawUrlEncodedAddress): string
    {
        $uri = $this->extConf->getGoogleMapsGeocodeUri();

        if ($uri === '') {
            return '';
        }

        if ($rawUrlEncodedAddress === '') {
            return $uri;
        }

        return sprintf($uri, $rawUrlEncodedAddress);
    }
}

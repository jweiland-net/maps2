<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Client\Request\GoogleMaps;

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

use JWeiland\Maps2\Client\Request\AbstractRequest;

/**
 * A Request class for Google Maps Geocode API
 */
class GeocodeRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $uri = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s';

    /**
     * Get URI for Geocode
     *
     * @return string
     * @throws \Exception
     */
    public function getUri(): string
    {
        return sprintf(
            $this->uri,
            $this->updateAddressForUri(
                (string)$this->getParameter('address')
            ),
            $this->extConf->getGoogleMapsGeocodeApiKey()
        );
    }
}

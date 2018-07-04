<?php
namespace JWeiland\Maps2\Client\Request;

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
     * @var string
     */
    protected $address = '';

    /**
     * Get URI for Geocode
     *
     * @return string
     * @throws \Exception
     */
    public function getUri()
    {
        return sprintf(
            $this->uri,
            $this->updateAddressForUri((string)$this->getParameter('address')),
            $this->extConf->getGoogleMapsGeocodeApiKey()
        );
    }

    /**
     * Set the address to get Lat/Lng for
     *
     * @param string $address
     * @return void
     */
    public function setAddress($address)
    {
        $this->addParameter('address', (string)$address);
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client\Request\OpenStreetMap;

use JWeiland\Maps2\Client\Request\AbstractRequest;
use JWeiland\Maps2\Configuration\ExtConf;

/**
 * A Request class for OpenStreetMap Geocode API
 */
class GeocodeRequest extends AbstractRequest
{
    public function __construct(
        protected ExtConf $extConf,
    ) {}

    /**
     * Get URI for Geocode
     *
     * @throws \Exception
     */
    public function getUri(): string
    {
        $uri = $this->extConf->getOpenStreetMapGeocodeUri();

        if ($uri === '') {
            return '';
        }

        if (!$this->hasParameter('address')) {
            return $uri;
        }

        return sprintf(
            $uri,
            $this->updateAddressForUri(
                (string)$this->getParameter('address'),
                $this->extConf->getDefaultCountry(),
            ),
        );
    }
}

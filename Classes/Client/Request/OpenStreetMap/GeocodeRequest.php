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
 * A Request class for Open Street Map Geocode API
 */
class GeocodeRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $uri = '';

    public function __construct(ExtConf $extConf = null)
    {
        parent::__construct($extConf);
        $this->uri = $this->extConf->getOpenStreetMapGeocodeUri();
    }

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
            )
        );
    }
}

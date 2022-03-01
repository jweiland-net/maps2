<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Service;

use JWeiland\Maps2\Helper\MapHelper;

/**
 * A non extbase orientated service which you can use from nearly everywhere,
 * to check, if a Map should be shown in FE or not.
 *
 * @deprecated
 */
class MapProviderRequestService
{
    protected MapHelper $mapHelper;

    public function __construct(MapHelper $mapHelper)
    {
        $this->mapHelper = $mapHelper;
    }

    /**
     * @deprecated
     */
    public function isRequestToMapProviderAllowed(): bool
    {
        trigger_error('Method MapProviderRequestService::isRequestToMapProviderAllowed is deprecated and has been moved to MapHelper::isRequestToMapProviderAllowed.', E_USER_DEPRECATED);

        return $this->mapHelper->isRequestToMapProviderAllowed();
    }
}

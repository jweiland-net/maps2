<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client\Request;

use JWeiland\Maps2\Configuration\MapProviderEnum;

/**
 * Interface for Requests to Map Providers
 */
interface RequestInterface
{
    public function canProcess(MapProviderEnum $mapProvider): bool;

    public function getUri(string $rawUrlEncodedAddress): string;
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Configuration;

enum MapProviderEnum: string
{
    case GOOGLE_MAPS = 'gm';
    case OPEN_STREET_MAP = 'osm';
}

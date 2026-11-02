<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client;

/**
 * Provider independent reason why a geocoding request failed.
 * Use it to show a meaningful message to the user without exposing
 * the technical (and maybe sensitive) message of the map provider.
 */
enum ClientErrorEnum: string
{
    // The map provider was asked successfully, but did not find any position
    case NO_POSITIONS_FOUND = 'noPositionsFound';

    // The map provider rejected the request (invalid API key, IP or referrer restrictions, ...)
    case REQUEST_DENIED = 'requestDenied';

    // The request limit or the quota of the map provider was exceeded
    case QUOTA_EXCEEDED = 'quotaExceeded';

    // The map provider did not accept the request, e.g. because of missing parameters
    case INVALID_REQUEST = 'invalidRequest';

    // The response of the map provider could not be processed
    case INVALID_RESPONSE = 'invalidResponse';

    // The map provider could not be reached or answered with an unknown error
    case SERVICE_ERROR = 'serviceError';
}

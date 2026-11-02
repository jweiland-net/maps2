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
 * Error of a request to the map provider.
 *
 * The message is technical, in English only and may contain sensitive data
 * like the IP address of the server. Do not show it to frontend users. Use
 * the provider independent type to show a meaningful message instead.
 */
final readonly class ClientError
{
    public function __construct(
        public ClientErrorEnum $type,
        public string $message,
        public string $title,
    ) {}
}

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
 * Result of a request to the map provider: the decoded response or the error
 */
final readonly class ClientResult
{
    /**
     * @param array<mixed> $response Decoded response of the map provider. Empty, if an error occurs.
     */
    public function __construct(
        public array $response = [],
        public ?ClientError $error = null,
    ) {}

    public function hasError(): bool
    {
        return $this->error instanceof ClientError;
    }
}

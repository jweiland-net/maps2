<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client\Request;

/**
 * Interface for Requests to Map Providers
 */
interface RequestInterface
{
    public function getUri(): string;

    public function isValidRequest(): bool;

    public function getParameters(): array;

    public function setParameters(array $parameters);

    public function addParameter(string $parameter, mixed $value);

    /**
     * @return mixed
     */
    public function getParameter(string $parameter);

    public function hasParameter(string $parameter): bool;
}

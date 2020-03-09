<?php
declare(strict_types = 1);
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
 * Interface for Requests to Map Providers
 */
interface RequestInterface
{
    public function getUri(): string;

    public function isValidRequest(): bool;

    public function getParameters(): array;

    public function setParameters(array $parameters);

    /**
     * @param string $parameter
     * @param mixed $value
     */
    public function addParameter(string $parameter, $value);

    /**
     * @param string $parameter
     * @return mixed
     */
    public function getParameter(string $parameter);

    public function hasParameter(string $parameter): bool;
}

<?php
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
 * Interface for all Google Maps Requests
 */
interface RequestInterface
{
    /**
     * Returns the uri
     *
     * @return string $uri
     */
    public function getUri();

    /**
     * Check, if current Request is valid
     *
     * @return bool
     */
    public function isValidRequest();
}

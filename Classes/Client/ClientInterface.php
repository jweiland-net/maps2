<?php
namespace JWeiland\Maps2\Client;

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

use JWeiland\Maps2\Client\Request\RequestInterface;

/**
 * Interface for Maps2 Clients
 */
interface ClientInterface
{
    /**
     * Process Google Maps Requests
     *
     * @param RequestInterface $request
     * @return mixed
     * @throws \Exception
     */
    public function processRequest(RequestInterface $request);
}

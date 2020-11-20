<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client;

use JWeiland\Maps2\Client\Request\RequestInterface;

/**
 * Interface for Maps2 Clients
 */
interface ClientInterface
{
    /**
     * @param RequestInterface $request
     * @return mixed
     * @throws \Exception
     */
    public function processRequest(RequestInterface $request);
}

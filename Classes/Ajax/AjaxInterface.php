<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Ajax;

/**
 * Interface for Ajax Requests
 */
interface AjaxInterface
{
    /**
     * process ajax request
     *
     * @param array $arguments Arguments to process
     * @param string $hash A generated hash value to verify that there are no modifications in the uri
     * @return string
     */
    public function processAjaxRequest(array $arguments, string $hash): string;
}

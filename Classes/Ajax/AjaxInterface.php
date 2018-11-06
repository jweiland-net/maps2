<?php
namespace JWeiland\Maps2\Ajax;

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

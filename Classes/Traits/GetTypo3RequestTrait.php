<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Traits;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequestFactory;

/**
 * Trait to get current TYPO3 request where not available by API
 */
trait GetTypo3RequestTrait
{
    /**
     * This method returns the TYPO3_REQUEST from globals if available,
     * otherwise falls back to creating a minimized ServerRequest from global variables
     * containing only server variables like GET, POST, and COOKIE data.
     */
    private function getTypo3Request(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
    }
}

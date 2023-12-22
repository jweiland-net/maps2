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
    private function getTypo3Request(): ServerRequestInterface
    {
        if ($GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface) {
            return $GLOBALS['TYPO3_REQUEST'];
        }

        // Build up a minified version with just the server variables like GET, POST, COOKIE
        return ServerRequestFactory::fromGlobals();
    }
}

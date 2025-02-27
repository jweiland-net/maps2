<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Frontend\Event\ShouldUseCachedPageDataIfAvailableEvent;/**

/**
 * We load GetInfoWindowContentMiddleware at a very late position as we need the TypoScript configuration.
 * At that position the PrepareTypoScriptFrontendRendering middleware of TYPO3 checks, if there is cached
 * page content available. If yes, a very minimum of TypoScript will be provided. In that specific state
 * plugin.tx_maps2 is missing. With this EventListener we disallow TYPO3 to use cached content for our
 * GetInfoWindowContentMiddleware. We need the contained paths from TypoScript for Fluid rendering.
 */
#[AsEventListener(identifier: 'maps2/deactivate-page-cache-usage')]
class PreventPageCacheForMiddlewareEventListener
{
    public function __invoke(ShouldUseCachedPageDataIfAvailableEvent $event): void
    {
        // Only if header matches we have to return "false" to set TYPO3s "$isUsingPageCacheAllowed" to false
        $event->setShouldUseCachedPageData(
            $event->getRequest()->getHeader('ext-maps2') !== ['infoWindowContent']
        );
    }
}

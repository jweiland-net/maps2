<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers\Cache;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Service\CacheService;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * A ViewHelper to check, if a cache entry exists in maps2 cache
 */
class HasCacheViewHelper extends AbstractViewHelper
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly FrontendInterface $cache
    ) {}

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'prefix',
            'string',
            'A prefix for the cache identifier.',
            false,
            'infoWindow',
        );
        $this->registerArgument(
            'poiCollection',
            PoiCollection::class,
            'We need the PoiCollection to build a better language independent CacheIdentifier.',
            true,
        );
    }

    /**
     * Checks if caching framework has the requested cache entry
     */
    public function render(): bool
    {
        $poiCollection = $this->cacheService->preparePoiCollectionForCacheMethods(
            $this->arguments['poiCollection']
        );

        try {
            return $this->cache->has(
                $this->cacheService->getCacheIdentifier(
                    $poiCollection,
                    $this->arguments['prefix']
                )
            );
        } catch (\Exception) {
        }

        return false;
    }
}

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
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * A ViewHelper to check, if a cache entry exists in maps2 cache
 */
class HasCacheViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument(
            'prefix',
            'string',
            'A prefix for the cache identifier.',
            false,
            'infoWindow'
        );
        $this->registerArgument(
            'poiCollection',
            PoiCollection::class,
            'We need the PoiCollection to build a better language independent CacheIdentifier.',
            true
        );
    }

    /**
     * Checks if caching framework has the requested cache entry
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return bool
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        $poiCollection = $cacheService->preparePoiCollectionForCacheMethods($arguments['poiCollection']);
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('maps2_cachedhtml');

        return $cache->has(
            $cacheService->getCacheIdentifier(
                $poiCollection,
                $arguments['prefix']
            )
        );
    }
}

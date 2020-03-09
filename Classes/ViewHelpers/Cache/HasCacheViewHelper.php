<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\ViewHelpers\Cache;

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

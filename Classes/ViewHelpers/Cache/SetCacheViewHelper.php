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
 * A ViewHelper to set a value to maps2 cache
 */
class SetCacheViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * The result of this ViewHelper should not be escaped
     *
     * @var bool
     */
    protected $escapeOutput = false;

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
        $this->registerArgument(
            'data',
            'string',
            'The data to be stored',
            true
        );
        $this->registerArgument(
            'tags',
            'array',
            'Tags to associate with this cache entry',
            false,
            []
        );
        $this->registerArgument(
            'lifetime',
            'int',
            'Lifetime of this cache entry in seconds. If null is specified, the default lifetime is used. "0" means unlimited lifetime',
            false
        );
    }

    /**
     * Saves data in a cache file.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        $poiCollection = $cacheService->preparePoiCollectionForCacheMethods($arguments['poiCollection']);
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('maps2_cachedhtml');

        $cache->set(
            $cacheService->getCacheIdentifier($poiCollection, $arguments['prefix']),
            $arguments['data'],
            $cacheService->getCacheTags($poiCollection, $arguments['tags']),
            ($arguments['lifetime'] === null ? null : (int)$arguments['lifetime'])
        );
    }
}

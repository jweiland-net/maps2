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
 * A ViewHelper to get a value from maps2 cache
 */
class GetCacheViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * The result of this ViewHelper should not be escaped
     *
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
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
     * Returns cache entry by given cache identifier
     * Info: here is no check if cache entry exists. Please use maps:cache.hasCache instead/before
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        $poiCollection = $cacheService->preparePoiCollectionForCacheMethods($arguments['poiCollection']);
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('maps2_cachedhtml');

        return $cache->get(
            $cacheService->getCacheIdentifier(
                $poiCollection,
                $arguments['prefix']
            )
        );
    }
}

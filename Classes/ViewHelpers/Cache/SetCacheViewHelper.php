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
use JWeiland\Maps2\Utility\CacheUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
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

    /**
     * Initialize arguments.
     *
     * @throws \Exception
     */
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
     * @return void
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $poiCollection = ObjectAccess::getGettableProperties($arguments['poiCollection']);
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('maps2_cachedhtml');
        $cache->set(
            CacheUtility::getCacheIdentifier($poiCollection, $arguments['prefix']),
            $arguments['data'],
            CacheUtility::getCacheTags($poiCollection, $arguments['tags']),
            ($arguments['lifetime'] === null ? null : (int)$arguments['lifetime'])
        );
    }
}

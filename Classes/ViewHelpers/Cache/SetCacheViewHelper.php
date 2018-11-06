<?php
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

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        $this->registerArgument('cacheIdentifier', 'string', 'An identifier for this specific cache entry', true);
        $this->registerArgument('data', 'string', 'The data to be stored', true);
        $this->registerArgument('tags', 'array', 'Tags to associate with this cache entry', false, []);
        $this->registerArgument('lifetime', 'int', 'Lifetime of this cache entry in seconds. If null is specified, the default lifetime is used. "0" means unlimited lifetime');
    }

    /**
     * Saves data in a cache file.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string The formatted value
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('maps2_cachedhtml');
        $cache->set(
            $arguments['cacheIdentifier'],
            $arguments['data'],
            $arguments['tags'],
            ($arguments['lifetime'] === null ? null : (int)$arguments['lifetime'])
        );
        return '';
    }
}

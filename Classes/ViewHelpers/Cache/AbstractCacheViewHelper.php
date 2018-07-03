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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * An abstract class to initialize the maps2 caches
 */
class AbstractCacheViewHelper extends AbstractViewHelper
{
    /**
     * @var \TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend
     */
    protected $cache;

    /**
     * initializes the caching framework for this view helper
     */
    public function initialize()
    {
        $this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('maps2_cachedhtml');
    }
}

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

/**
 * A ViewHelper to check, if a cache entry exists in maps2 cache
 */
class HasCacheViewHelper extends AbstractCacheViewHelper
{

    /**
     * checks if caching framework has the requested cache entry
     *
     * @param string $cacheIdentifier String to identify the cache entry
     * @return bool
     */
    public function render($cacheIdentifier)
    {
        return $this->cache->has($cacheIdentifier);
    }
}

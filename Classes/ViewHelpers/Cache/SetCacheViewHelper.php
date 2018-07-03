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
 * A ViewHelper to set a value to maps2 cache
 */
class SetCacheViewHelper extends AbstractCacheViewHelper
{
    /**
     * Saves data in a cache file.
     *
     * @param string $cacheIdentifier An identifier for this specific cache entry
     * @param string $data The data to be stored
     * @param array $tags Tags to associate with this cache entry
     * @param int $lifetime Lifetime of this cache entry in seconds. If null is specified, the default lifetime is used. "0" means unlimited liftime.
     *
     * @return void
     */
    public function render($cacheIdentifier, $data, array $tags = [], $lifetime = null)
    {
        $this->cache->set($cacheIdentifier, $data, $tags, ($lifetime === null ? null : (int)$lifetime));
    }
}

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
 * A ViewHelper to get a value from maps2 cache
 */
class GetCacheViewHelper extends AbstractCacheViewHelper
{
    /**
     * The result of this ViewHelper should not be escaped
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * returns cache entry by given cache identifier
     * Info: here is no check if cache entry exists. Please use maps:cache.hasCache instead/before
     *
     * @param string $cacheIdentifier String to identify the cache entry
     *
     * @return string
     */
    public function render($cacheIdentifier)
    {
        return $this->cache->get($cacheIdentifier);
    }
}

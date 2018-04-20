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
 * Class HasCacheViewHelper
 *
 * @category ViewHelpers/Cache
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
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

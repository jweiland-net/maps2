<?php
namespace JWeiland\Maps2\ViewHelpers\Cache;

/**
 * This file is part of the TYPO3 CMS project.
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
 * Class GetCacheViewHelper
 *
 * @category ViewHelpers/Cache
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
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

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
 * Class SetCacheViewHelper
 *
 * @category ViewHelpers/Cache
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
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
     * @return void
     * @throws \TYPO3\CMS\Core\Cache\Exception if no cache frontend has been set.
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException if the data to be stored is not a string.
     */
    public function render($cacheIdentifier, $data, array $tags = array(), $lifetime = null)
    {
        $this->cache->set($cacheIdentifier, $data, $tags, ($lifetime === null ? null : (int)$lifetime));
    }
}

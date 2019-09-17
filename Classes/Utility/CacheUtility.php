<?php
namespace JWeiland\Maps2\Utility;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A global accessible class to build Cache Identifier and Tags for Cache Entries.
 * Used in Cache ViewHelpers and after storing PoiCollections in Backend: CreateMaps2RecordHook
 */
class CacheUtility
{
    /**
     * In previous versions our CacheIdentifier was infoWindow{PoiCollectionUid}.
     * In multilingual environments, where UID is always the same, we have to build a more unique
     * Cache Identifier.
     *
     * @param string $prefix A prefix you can prepend to the generated CacheIdentifier
     * @param array $poiCollection
     * @return string
     */
    public static function getCacheIdentifier(array $poiCollection, string $prefix = 'infoWindow'): string
    {
        return sprintf(
            '%s%s',
            $prefix,
            GeneralUtility::stdAuthCode(
                $poiCollection,
                'uid, pid, sys_language_uid, title, address',
                24
            )
        );
    }

    /**
     * Add UID and PID of PoiCollection as Cache-Tags to Cache-Entry.
     * Please do not use "infoWindowPid" and "infoWindowUid" as Cache-Tag-Prefix in your template,
     * as we will override them here.
     *
     * @param array $poiCollection
     * @param array $cacheTags
     * @return array
     */
    public static function getCacheTags(array $poiCollection, array $cacheTags = []): array
    {
        return array_merge(
            $cacheTags,
            [
                'infoWindowPid' . $poiCollection['pid'] ?? 0,
                'infoWindowUid' . $poiCollection['uid'] ?? 0,
            ]
        );
    }

}

<?php
namespace JWeiland\Maps2\Service;

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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A global accessible class to build Cache Identifier and Tags for Cache Entries.
 * Used in Cache ViewHelpers and after storing PoiCollections in Backend: CreateMaps2RecordHook
 */
class CacheService
{
    /**
     * In previous versions our CacheIdentifier was infoWindow{PoiCollectionUid}.
     * In multilingual environments, where UID is always the same, we have to build a more unique
     * Cache Identifier.
     *
     * @param string $prefix A prefix you can prepend to the generated CacheIdentifier
     * @param array $poiCollection
     * @return string
     * @throws \Exception
     */
    public function getCacheIdentifier(array $poiCollection, string $prefix = 'infoWindow'): string
    {
        if (!$this->isFrontendEnvironment()) {
            throw new \Exception('getCacheIdentifier can only be called from FE, as we have to add the true language ID to PoiCollection');
        }

        // We do not add the original sys_language_uid of PoiCollection, as it can be the same for different languages.
        $poiCollection['language'] = $this->getLanguageUid();

        return sprintf(
            '%s%s',
            $prefix,
            GeneralUtility::stdAuthCode(
                $poiCollection,
                'uid, pid, language, title, address',
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
    public function getCacheTags(array $poiCollection, array $cacheTags = []): array
    {
        return array_merge(
            $cacheTags,
            [
                'infoWindowPid' . $poiCollection['pid'] ?? 0,
                'infoWindowUid' . $poiCollection['uid'] ?? 0,
            ]
        );
    }

    /**
     * In case of hooks where we have PoiCollection as array, we can assign PoiCollection directly
     * to getCacheIdentifier. But in case of ViewHelpers we have a PoiCollection object. You can use
     * this method to prepare/sanitize PoiCollection objects for use with getCacheIdentifier/getCacheTags.
     *
     * @param PoiCollection $poiCollection
     * @return array
     */
    public function preparePoiCollectionForCacheMethods(PoiCollection $poiCollection): array
    {
        return [
            'uid' => $poiCollection->getUid(),
            'pid' => $poiCollection->getUid(),
            'title' => $poiCollection->getTitle(),
            'address' => $poiCollection->getAddress()
        ];
    }

    /**
     * Returns the calculated (incl. fallback) sys_language_uid
     *
     * @return int
     * @throws \Exception
     */
    protected function getLanguageUid(): int
    {
        if (!$this->isFrontendEnvironment()) {
            throw new \Exception('getLanguageId can only be called from FE, as we have to add the true language ID to PoiCollection');
        }

        if (version_compare(TYPO3_branch, '9.4', '<')) {
            $languageId = (int)$GLOBALS['TSFE']->sys_language_uid;
        } else {
            $context = GeneralUtility::makeInstance(Context::class);
            $languageId = (int)$context->getPropertyFromAspect('language', 'id');
        }
        return $languageId;
    }

    /**
     * Check, if we are in Frontend environment
     *
     * @return bool
     */
    protected function isFrontendEnvironment(): bool
    {
        return (defined('TYPO3_MODE') && TYPO3_MODE === 'FE') ?: false;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\HashAlgo;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Http\ApplicationType;

/**
 * A global accessible class to build Cache Identifier and Tags for Cache Entries.
 * Used in Cache ViewHelpers and after storing PoiCollections in Backend: CreateMaps2RecordHook
 */
readonly class CacheService
{
    public function __construct(
        protected HashService $hashService,
        protected Context $context,
    ) {}

    /**
     * In previous versions our CacheIdentifier was infoWindow{PoiCollectionUid}.
     * In multilingual environments, where UID is always the same, we have to build a more unique
     * Cache Identifier.
     *
     * @throws \Exception
     */
    public function getCacheIdentifier(
        array $poiCollection,
        string $prefix,
        ServerRequestInterface $request,
    ): string {
        if (!$this->isFrontendEnvironment($request)) {
            throw new \RuntimeException(
                'getCacheIdentifier can only be called from FE, as we have to add the true language ID to PoiCollection',
                1733471017,
            );
        }

        // We do not add the original sys_language_uid of PoiCollection, as it can be the same for different languages.
        $poiCollection['language'] = $this->getLanguageUid($request);

        return sprintf(
            '%s%s',
            $prefix,
            $this->hashService->hmac(
                \json_encode(array_diff_key($poiCollection, ['uid', 'pid', 'language', 'title', 'address'])),
                $prefix,
                HashAlgo::SHA3_256,
            ),
        );
    }

    /**
     * Add UID and PID of PoiCollection as Cache-Tags to Cache-Entry.
     * Please do not use "infoWindowPid" and "infoWindowUid" as Cache-Tag-Prefix in your template,
     * as we will override them here.
     */
    public function getCacheTags(array $poiCollection, array $cacheTags = []): array
    {
        return array_merge(
            $cacheTags,
            [
                'infoWindowPid' . ($poiCollection['pid'] ?? 0),
                'infoWindowUid' . ($poiCollection['uid'] ?? 0),
            ],
        );
    }

    /**
     * Returns the calculated (incl. fallback) sys_language_uid
     *
     * @throws \Exception
     */
    protected function getLanguageUid(ServerRequestInterface $request): int
    {
        if (!$this->isFrontendEnvironment($request)) {
            throw new \RuntimeException(
                'getLanguageId can only be called from FE, as we have to add the true language ID to PoiCollection',
                1733470968,
            );
        }

        return (int)$this->context->getPropertyFromAspect('language', 'id');
    }

    protected function isFrontendEnvironment(ServerRequestInterface $request): bool
    {
        return ApplicationType::fromRequest($request)->isFrontend();
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers\Cache;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Service\CacheService;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * A ViewHelper to set a value to maps2 cache
 */
class SetCacheViewHelper extends AbstractViewHelper
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly FrontendInterface $cache
    ) {}

    /**
     * The result of this ViewHelper should not be escaped
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'prefix',
            'string',
            'A prefix for the cache identifier.',
            false,
            'infoWindow',
        );
        $this->registerArgument(
            'poiCollection',
            PoiCollection::class,
            'We need the PoiCollection to build a better language independent CacheIdentifier.',
            true,
        );
        $this->registerArgument(
            'data',
            'string',
            'The data to be stored',
            true,
        );
        $this->registerArgument(
            'tags',
            'array',
            'Tags to associate with this cache entry',
            false,
            [],
        );
        $this->registerArgument(
            'lifetime',
            'int',
            'Lifetime of this cache entry in seconds. If null is specified, the default lifetime is used. "0" means unlimited lifetime',
        );
    }

    /**
     * Saves data in a cache file.
     */
    public function render(): void
    {
        $poiCollection = $this->cacheService->preparePoiCollectionForCacheMethods(
            $this->arguments['poiCollection']
        );

        try {
            $this->cache->set(
                $this->cacheService->getCacheIdentifier($poiCollection, $this->arguments['prefix']),
                $this->arguments['data'],
                $this->cacheService->getCacheTags($poiCollection, $this->arguments['tags']),
                ($this->arguments['lifetime'] === null ? null : (int)$this->arguments['lifetime'])
            );
        } catch (\Exception) {
        }
    }
}

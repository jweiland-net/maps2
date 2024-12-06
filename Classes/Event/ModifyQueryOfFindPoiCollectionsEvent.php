<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Event;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/*
 * Use this event, if you want to modify the query
 * of PoiCollectionRepository::findPoiCollections
 */
class ModifyQueryOfFindPoiCollectionsEvent
{
    public function __construct(
        protected QueryBuilder $queryBuilder,
        protected array $settings,
        protected int $poiCollectionUid
    ) {}

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getPoiCollectionUid(): int
    {
        return $this->poiCollectionUid;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Event;

/**
 * Event to modify a POI collection record after saving a foreign location record
 */
class PostProcessPoiCollectionRecordEvent
{
    protected string $poiCollectionTableName = '';

    protected int $poiCollectionUid = 0;

    protected string $foreignTableName = '';

    protected array $foreignLocationRecord = [];

    protected array $options = [];

    public function __construct(
        string $poiCollectionTableName,
        int $poiCollectionUid,
        string $foreignTableName,
        array $foreignLocationRecord,
        array $options,
    ) {
        $this->poiCollectionTableName = $poiCollectionTableName;
        $this->poiCollectionUid = $poiCollectionUid;
        $this->foreignTableName = $foreignTableName;
        $this->foreignLocationRecord = $foreignLocationRecord;
        $this->options = $options;
    }

    public function getPoiCollectionTableName(): string
    {
        return $this->poiCollectionTableName;
    }

    public function getPoiCollectionUid(): int
    {
        return $this->poiCollectionUid;
    }

    public function getForeignTableName(): string
    {
        return $this->foreignTableName;
    }

    public function getForeignLocationRecord(): array
    {
        return $this->foreignLocationRecord;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}

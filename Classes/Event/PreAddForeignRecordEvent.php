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
 * Event to modify a foreign record just before it was added to PoiCollection object
 */
class PreAddForeignRecordEvent
{
    protected array $foreignRecord = [];

    protected string $tableName = '';

    protected string $columnName = '';

    public function __construct(
        array $foreignRecord,
        string $tableName,
        string $columnName
    ) {
        $this->foreignRecord = $foreignRecord;
        $this->tableName = $tableName;
        $this->columnName = $columnName;
    }

    public function getForeignRecord(): array
    {
        return $this->foreignRecord;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function setForeignRecord(array $foreignRecord): void
    {
        $this->foreignRecord = $foreignRecord;
    }
}

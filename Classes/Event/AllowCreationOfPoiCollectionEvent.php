<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Event;

use JWeiland\Maps2\Tca\ColumnRegistration;

/**
 * Event to control if a PoiCollection is allowed to be created while saving a foreign record in TYPO3 backend.
 */
final class AllowCreationOfPoiCollectionEvent
{
    public function __construct(
        private readonly array $foreignLocationRecord,
        private readonly string $foreignTableName,
        private readonly string $foreignColumnName,
        private readonly ColumnRegistration $columnRegistration,
        private bool $isValid,
    ) {}

    public function getForeignLocationRecord(): array
    {
        return $this->foreignLocationRecord;
    }

    public function getForeignTableName(): string
    {
        return $this->foreignTableName;
    }

    public function getForeignColumnName(): string
    {
        return $this->foreignColumnName;
    }

    public function getColumnRegistration(): ColumnRegistration
    {
        return $this->columnRegistration;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): void
    {
        $this->isValid = $isValid;
    }
}

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
 * Event to control, if a PoiCollection is allowed to be created while saving a foreign record in TYPO3 backend.
 */
class AllowCreationOfPoiCollectionEvent
{
    public function __construct(
        protected array $foreignLocationRecord,
        protected string $foreignTableName,
        protected string $foreignColumnName,
        protected array $options,
        protected bool $isValid
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

    public function getOptions(): array
    {
        return $this->options;
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

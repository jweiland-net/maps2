<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tca;

/**
 * Resolves the value that has to be synchronized from a foreign record into a POI collection column.
 *
 * Can either be a single column (leaf), or a composite of further ForeignColumn instances which are
 * combined with a COALESCE (first non-empty value wins) or CONCAT (join all non-empty values) strategy.
 */
final readonly class ForeignColumn
{
    /**
     * @param ForeignColumn[] $children
     */
    private function __construct(
        private ForeignColumnResolveTypeEnum $resolveType,
        private string $columnName = '',
        private array $children = [],
        private string $glue = ' ',
    ) {}

    /**
     * Builds a ForeignColumn from the raw `foreignColumnName` TCA configuration. Accepts:
     *
     * - a plain string with the column name of the foreign table
     * - an array with `type` (a ForeignColumnResolveTypeEnum case or its string value: coalesce|concat)
     *   and `columns`, where every entry of `columns` may again be a column name or a nested
     *   configuration array
     * - a plain list of column names / nested configuration arrays, which is treated as an
     *   implicit `coalesce`: the first entry that resolves to a non-empty value wins
     */
    public static function createFromConfiguration(mixed $configuration): ?self
    {
        if (is_string($configuration)) {
            $columnName = trim($configuration);
            if ($columnName === '') {
                return null;
            }

            return new self(ForeignColumnResolveTypeEnum::SINGLE, columnName: $columnName);
        }

        if (!is_array($configuration)) {
            return null;
        }

        if (isset($configuration['type'], $configuration['columns']) && is_array($configuration['columns'])) {
            $resolveType = $configuration['type'] instanceof ForeignColumnResolveTypeEnum
                ? $configuration['type']
                : ForeignColumnResolveTypeEnum::tryFrom((string)$configuration['type']);

            if (!$resolveType instanceof ForeignColumnResolveTypeEnum || $resolveType === ForeignColumnResolveTypeEnum::SINGLE) {
                return null;
            }

            $children = self::createChildren($configuration['columns']);
            if ($children === []) {
                return null;
            }

            return new self(
                $resolveType,
                children: $children,
                glue: (string)($configuration['glue'] ?? ' '),
            );
        }

        if (array_is_list($configuration)) {
            $children = self::createChildren($configuration);
            if ($children === []) {
                return null;
            }

            return new self(ForeignColumnResolveTypeEnum::COALESCE, children: $children);
        }

        return null;
    }

    /**
     * @return ForeignColumn[]
     */
    private static function createChildren(array $columns): array
    {
        return array_values(array_filter(array_map(
            static fn(mixed $column): ?self => self::createFromConfiguration($column),
            $columns,
        )));
    }

    public function resolveValue(array $record): string
    {
        return match ($this->resolveType) {
            ForeignColumnResolveTypeEnum::SINGLE => trim((string)($record[$this->columnName] ?? '')),
            ForeignColumnResolveTypeEnum::COALESCE => $this->resolveCoalesce($record),
            ForeignColumnResolveTypeEnum::CONCAT => $this->resolveConcat($record),
        };
    }

    /**
     * Flat list of every column name involved in this (possibly nested) configuration.
     * Used to validate against TCA of the foreign table.
     *
     * @return string[]
     */
    public function getColumnNames(): array
    {
        if ($this->resolveType === ForeignColumnResolveTypeEnum::SINGLE) {
            return [$this->columnName];
        }

        $columnNames = [];
        foreach ($this->children as $child) {
            $columnNames[] = $child->getColumnNames();
        }

        return array_values(array_unique(array_merge([], ...$columnNames)));
    }

    private function resolveCoalesce(array $record): string
    {
        foreach ($this->children as $child) {
            $value = $child->resolveValue($record);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function resolveConcat(array $record): string
    {
        $values = array_filter(
            array_map(
                static fn(self $child): string => $child->resolveValue($record),
                $this->children,
            ),
            static fn(string $value): bool => $value !== '',
        );

        return implode($this->glue, $values);
    }
}

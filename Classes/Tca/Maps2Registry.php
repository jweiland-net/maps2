<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tca;

use TYPO3\CMS\Core\DataHandling\TableColumnType;
use TYPO3\CMS\Core\Schema\Field\GroupFieldType;
use TYPO3\CMS\Core\Schema\TcaSchema;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;

/**
 * Class to register maps2 columns to TCA.
 */
readonly class Maps2Registry
{
    public function __construct(
        protected TcaSchemaFactory $tcaSchemaFactory,
    ) {}

    public function getColumnRegistry(): ColumnRegistrationStorage
    {
        $storage = new ColumnRegistrationStorage();

        foreach ($this->tcaSchemaFactory->all() as $tcaSchema) {
            if (!$tcaSchema instanceof TcaSchema) {
                continue;
            }

            foreach ($tcaSchema->getFieldsOfType(TableColumnType::GROUP) as $groupField) {
                if (!$groupField instanceof GroupFieldType) {
                    continue;
                }

                if (($groupField->getConfiguration()['renderType'] ?? '') !== 'maps2Relation') {
                    continue;
                }

                $columnMatches = $this->getColumnMatches(
                    $groupField->getConfiguration()['columnMatch'] ?? [],
                );

                $defaultStoragePid = $this->getDefaultStoragePid(
                    $groupField->getConfiguration()['defaultStoragePid'] ?? 0,
                );

                $synchronizeColumns = $this->getSynchronizeColumn(
                    $groupField->getConfiguration()['synchronizeColumns'] ?? [],
                );

                $columnRegistration = new ColumnRegistration(
                    tableName: $tcaSchema->getName(),
                    columnName: $groupField->getName(),
                    addressColumns: $groupField->getConfiguration()['addressColumns'] ?? [],
                    countryColumn: (string)($groupField->getConfiguration()['countryColumn'] ?? ''),
                    defaultCountry: (string)($groupField->getConfiguration()['defaultCountry'] ?? ''),
                    columnMatch: $columnMatches,
                    defaultStoragePid: $defaultStoragePid,
                    synchronizeColumns: $synchronizeColumns,
                    override: $groupField->getConfiguration()['override'] ?? false,
                );

                $storage->append($columnRegistration);
            }
        }

        return $storage;
    }

    /**
     * @return ColumnMatch[]
     */
    private function getColumnMatches(array $columnMatchConfiguration): array
    {
        $columnMatches = [];

        foreach ($columnMatchConfiguration as $columnName => $configuration) {
            if (is_scalar($configuration)) {
                $columnMatche = $this->createColumnMatch([
                    'columnName' => $columnName,
                    'expr' => 'eq',
                    'value' => $configuration,
                ]);

                if ($columnMatche instanceof ColumnMatch) {
                    $columnMatches[$columnName] = $columnMatche;
                }

                continue;
            }

            if (!isset($configuration['columnName'])) {
                $configuration['columnName'] = $columnName;
            }

            $columnMatche = $this->createColumnMatch($configuration);
            if ($columnMatche instanceof ColumnMatch) {
                $columnMatches[$columnName] = $columnMatche;
            }
        }

        return $columnMatches;
    }

    private function createColumnMatch(array $columnMatchConfiguration): ?ColumnMatch
    {
        if (
            isset($columnMatchConfiguration['columnName'])
            && isset($columnMatchConfiguration['expr'])
            && isset($columnMatchConfiguration['value'])
        ) {
            return new ColumnMatch(
                columnName: (string)$columnMatchConfiguration['columnName'] ?? '',
                expr: (string)$columnMatchConfiguration['expr'] ?? '',
                value: (string)$columnMatchConfiguration['value'] ?? '',
            );
        }

        return null;
    }

    /**
     * @return int|StoragePidLocation[]
     */
    private function getDefaultStoragePid(mixed $storagePidConfiguration): int|array
    {
        if (!is_array($storagePidConfiguration)) {
            return (int)$storagePidConfiguration;
        }

        $storagePidLocation = $this->createStoragePidLocation($storagePidConfiguration);
        if ($storagePidLocation instanceof StoragePidLocation) {
            return [$storagePidLocation];
        }

        $storagePidLocations = [];
        foreach ($storagePidConfiguration as $configuration) {
            $storagePidLocation = $this->createStoragePidLocation($configuration);
            if ($storagePidLocation instanceof StoragePidLocation) {
                $storagePidLocations[] = $storagePidLocation;
            }
        }

        return $storagePidLocations;
    }

    private function createStoragePidLocation(array $storagePidConfiguration): ?StoragePidLocation
    {
        if (
            isset($storagePidConfiguration['extKey'])
            && isset($storagePidConfiguration['property'])
            && isset($storagePidConfiguration['type'])
        ) {
            return new StoragePidLocation(
                extKey: (string)$storagePidConfiguration['extKey'] ?? '',
                property: (string)$storagePidConfiguration['property'] ?? '',
                type: StoragePidLocationTypeEnum::from((string)$storagePidConfiguration['type'] ?? ''),
            );
        }

        return null;
    }

    /**
     * @return SynchronizeColumn[]
     */
    private function getSynchronizeColumn(array $synchronizeColumnConfiguration): array
    {
        $synchronizeColumn = $this->createSynchronizeColumn($synchronizeColumnConfiguration);
        if ($synchronizeColumn instanceof SynchronizeColumn) {
            return [$synchronizeColumn];
        }

        $synchronizeColumns = [];
        foreach ($synchronizeColumnConfiguration as $configuration) {
            $synchronizeColumn = $this->createSynchronizeColumn($configuration);
            if ($synchronizeColumn instanceof SynchronizeColumn) {
                $synchronizeColumns[] = $synchronizeColumn;
            }
        }

        return $synchronizeColumns;
    }

    private function createSynchronizeColumn(array $synchronizeColumnConfiguration): ?SynchronizeColumn
    {
        if (
            isset($synchronizeColumnConfiguration['foreignColumnName'])
            && isset($synchronizeColumnConfiguration['poiCollectionColumnName'])
        ) {
            return new SynchronizeColumn(
                foreignColumnName: (string)$synchronizeColumnConfiguration['foreignColumnName'] ?? '',
                poiCollectionColumnName: (string)$synchronizeColumnConfiguration['poiCollectionColumnName'] ?? '',
            );
        }

        return null;
    }
}

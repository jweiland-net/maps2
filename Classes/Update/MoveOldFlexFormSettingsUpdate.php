<?php
declare(strict_types=1);
namespace JWeiland\Maps2\Update;

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

use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\AbstractUpdate;

/**
 * With maps2 5.0.0 we have moved some FlexForm Settings to another sheet.
 * To prevent duplicates in DB, this update wizard removes old settings from FlexForm.
 */
class MoveOldFlexFormSettingsUpdate extends AbstractUpdate
{
    /**
     * @var string Title of this updater
     */
    protected $title = '[maps2] Move old FlexForm fields to new FlexForm sheet';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description): bool
    {
        $description = 'With maps2 5.0.0 we have moved some FlexForm fields to another sheet.'
            . 'To prevent duplicates in DB, this update wizard removes old fields from FlexForm';

        $records = $this->getTtContentRecordsWithMaps2Plugin();
        foreach ($records as $record) {
            $valueFromDatabase = (string)$record['pi_flexform'] !== '' ? GeneralUtility::xml2array($record['pi_flexform']) : [];
            if (!is_array($valueFromDatabase)) {
                $valueFromDatabase = [];
            }

            if (array_key_exists('sDEFAULT', $valueFromDatabase['data'])) {
                return true;
            }

            $oldFieldNames = [
                'activateScrollWheel',
                'mapTypeControl',
                'mapTypeId',
                'scaleControl',
                'streetViewControl',
                'styles'
            ];

            foreach ($oldFieldNames as $oldFieldName) {
                if (array_key_exists('settings.' . $oldFieldName, $valueFromDatabase['data']['sMapOptions']['lDEF'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries Queries done in this update
     * @param string &$customMessage Custom message
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$dbQueries, &$customMessage): bool
    {
        $records = $this->getTtContentRecordsWithMaps2Plugin();
        foreach ($records as $record) {
            $valueFromDatabase = (string)$record['pi_flexform'] !== '' ? GeneralUtility::xml2array($record['pi_flexform']) : [];
            if (!is_array($valueFromDatabase)) {
                $valueFromDatabase = [];
            }
            $this->moveSheetDefaultToDef($valueFromDatabase);
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.activateScrollWheel', 'sMapOptions', 'sGoogleMapsOptions');
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.mapTypeControl', 'sMapOptions', 'sGoogleMapsOptions');
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.mapTypeId', 'sMapOptions', 'sGoogleMapsOptions');
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.scaleControl', 'sMapOptions', 'sGoogleMapsOptions');
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.streetViewControl', 'sMapOptions', 'sGoogleMapsOptions');
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.styles', 'sMapOptions', 'sGoogleMapsOptions');
            unset($valueFromDatabase['data']['sGoogleMapsOptions']['lDEF']['settings.fullScreenControl']);

            $connection = $this->getConnectionPool()->getConnectionForTable('tt_content');
            $connection->update(
                'tt_content',
                [
                    'pi_flexform' => $this->checkValue_flexArray2Xml($valueFromDatabase)
                ],
                [
                    'uid' => (int)$record['uid']
                ],
                [
                    'pi_flexform' => \PDO::PARAM_STR
                ]
            );
        }

        return true;
    }

    /**
     * Get all (incl. deleted/hidden) tt_content records with plugin maps2_maps2
     *
     * @return array
     */
    protected function getTtContentRecordsWithMaps2Plugin(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $records = $queryBuilder
            ->select('uid', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter('list', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'list_type',
                    $queryBuilder->createNamedParameter('maps2_maps2', \PDO::PARAM_STR)
                )
            )
            ->execute()
            ->fetchAll();

        if ($records === false) {
            $records = [];
        }

        return $records;
    }

    /**
     * It's not a must have, but sDEF seems to be more default than sDEFAULT as first sheet name in TYPO3
     *
     * @param array $valueFromDatabase
     */
    protected function moveSheetDefaultToDef(array &$valueFromDatabase)
    {
        if (array_key_exists('sDEFAULT', $valueFromDatabase['data'])) {
            foreach ($valueFromDatabase['data']['sDEFAULT']['lDEF'] as $field => $value) {
                $this->moveFieldFromOldToNewSheet($valueFromDatabase, $field, 'sDEFAULT', 'sDEF');
            }

            // remove old sheet completely
            unset($valueFromDatabase['data']['sDEFAULT']);
        }
    }

    /**
     * Move field from one sheet to another and remove field from old location
     *
     * @param array $valueFromDatabase
     * @param string $field
     * @param string $oldSheet
     * @param string $newSheet
     */
    protected function moveFieldFromOldToNewSheet(array &$valueFromDatabase, string $field, string $oldSheet, string $newSheet)
    {
        if (array_key_exists($field, $valueFromDatabase['data'][$oldSheet]['lDEF'])) {
            // Create base sheet, if not exist
            if (!array_key_exists($newSheet, $valueFromDatabase['data'])) {
                $valueFromDatabase['data'][$newSheet] = [
                    'lDEF' => []
                ];
            }

            // Move field to new location, if not already done
            if (!array_key_exists($field, $valueFromDatabase['data'][$newSheet]['lDEF'])) {
                $valueFromDatabase['data'][$newSheet]['lDEF'][$field] = $valueFromDatabase['data'][$oldSheet]['lDEF'][$field];
            }

            // Remove old reference
            unset($valueFromDatabase['data'][$oldSheet]['lDEF'][$field]);
        }
    }

    /**
     * Converts an array to FlexForm XML
     *
     * @param array $array Array with FlexForm data
     * @return string Input array converted to XML
     */
    public function checkValue_flexArray2Xml($array): string
    {
        /** @var $flexObj FlexFormTools */
        $flexObj = GeneralUtility::makeInstance(FlexFormTools::class);
        return $flexObj->flexArray2Xml($array, true);
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Update;

use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * With maps2 5.0.0 we have moved some FlexForm Settings to another sheet.
 * To prevent duplicates in DB, this update wizard removes old settings from FlexForm.
 */
class MoveOldFlexFormSettingsUpdate implements UpgradeWizardInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'maps2MoveFlexFormFields';
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return '[maps2] Move old FlexForm fields to new FlexForm sheet';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'It seems that some fields from FlexForm of one Map Provider was available for all Map Providers now. ' .
            'In that case we have to move these fields to another Sheet.';
    }

    /**
     * @return bool
     */
    public function updateNecessary(): bool
    {
        $records = $this->getTtContentRecordsWithMaps2Plugin();
        foreach ($records as $record) {
            $valueFromDatabase = (string)$record['pi_flexform'] !== '' ? GeneralUtility::xml2array($record['pi_flexform']) : [];
            if (
                !is_array($valueFromDatabase)
                || empty($valueFromDatabase)
                || !isset($valueFromDatabase['data'])
                || !is_array($valueFromDatabase['data'])
            ) {
                continue;
            }

            if (array_key_exists('sDEFAULT', $valueFromDatabase['data'])) {
                return true;
            }

            try {
                if (
                    ArrayUtility::getValueByPath(
                        $valueFromDatabase,
                        'data/sGoogleMapsOptions/lDEF/settings.activateScrollWheel'
                    )
                ) {
                    return true;
                }
            } catch (MissingArrayPathException $e) {
                // If value does not exist, check further requirements
            } catch (\RuntimeException $e) {
                // Some as above, but for TYPO3 8
            }

            if (
                !isset($valueFromDatabase['data']['sMapOptions']['lDEF'])
                || !is_array($valueFromDatabase['data']['sMapOptions']['lDEF'])
            ) {
                continue;
            }

            $oldFieldNames = [
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
     * @return bool
     */
    public function executeUpdate(): bool
    {
        $records = $this->getTtContentRecordsWithMaps2Plugin();
        foreach ($records as $record) {
            $valueFromDatabase = (string)$record['pi_flexform'] !== '' ? GeneralUtility::xml2array($record['pi_flexform']) : [];
            if (!is_array($valueFromDatabase) || empty($valueFromDatabase)) {
                continue;
            }
            $this->moveSheetDefaultToDef($valueFromDatabase);
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.activateScrollWheel', 'sGoogleMapsOptions', 'sMapOptions');
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
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
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
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq(
                        'list_type',
                        $queryBuilder->createNamedParameter('maps2_citymap', \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'list_type',
                        $queryBuilder->createNamedParameter('maps2_maps2', \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'list_type',
                        $queryBuilder->createNamedParameter('maps2_searchwithinradius', \PDO::PARAM_STR)
                    )
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
        try {
            $value = ArrayUtility::getValueByPath(
                $valueFromDatabase,
                sprintf(
                    'data/%s/lDEF/%s',
                    $oldSheet,
                    $field
                )
            );

            // Create base sheet, if not exist
            if (!array_key_exists($newSheet, $valueFromDatabase['data'])) {
                $valueFromDatabase['data'][$newSheet] = [
                    'lDEF' => []
                ];
            }

            // Move field to new location, if not already done
            if (!array_key_exists($field, $valueFromDatabase['data'][$newSheet]['lDEF'])) {
                $valueFromDatabase['data'][$newSheet]['lDEF'][$field] = $value;
            }

            // Remove old reference
            unset($valueFromDatabase['data'][$oldSheet]['lDEF'][$field]);
        } catch (MissingArrayPathException $e) {
            // Path does not exist in Array. Do not update anything
        } catch (\RuntimeException $e) {
            // Some as above, but for TYPO3 8
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
        $flexObj = GeneralUtility::makeInstance(FlexFormTools::class);
        return $flexObj->flexArray2Xml($array, true);
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}

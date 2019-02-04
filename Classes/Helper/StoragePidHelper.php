<?php
declare(strict_types=1);
namespace JWeiland\Maps2\Helper;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * This class searches various places to find a storage PID where new POI Collections should be saved
 */
class StoragePidHelper
{
    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    /**
     * StoragePidHelper constructor.
     *
     * @param MessageHelper|null $messageHelper
     */
    public function __construct(MessageHelper $messageHelper = null)
    {
        if ($messageHelper === null) {
            $messageHelper = GeneralUtility::makeInstance(MessageHelper::class);
        }
        $this->messageHelper = $messageHelper;
    }

    /**
     * Gets default storage page where new POI collections should be stored
     *
     * @param array $foreignLocationRecord The foreign record with tx_maps2_uid column
     * @param array $options The configuration of Maps2 Registry
     * @return int
     */
    public function getDefaultStoragePidForNewPoiCollection(array $foreignLocationRecord, array $options): int
    {
        $defaultStoragePid = 0;
        $this->updateStoragePidFromForeignLocationRecord($defaultStoragePid, $foreignLocationRecord);
        $this->updateStoragePidFromMaps2Registry($defaultStoragePid, $options, $foreignLocationRecord);
        $this->updateDefaultStoragePidFromPageTsConfig($defaultStoragePid, $foreignLocationRecord);

        if (empty($defaultStoragePid)) {
            $this->messageHelper->addFlashMessage(
                'No PID found to store POI collection. Please check various places like pageTSconfig, Maps2 Registry and PID of this currently saved record.',
                'Can not find a valid PID to store EXT:maps2 records'
            );
        }

        return $defaultStoragePid;
    }

    /**
     * Lowest priority:
     * Get default location record from foreign location record
     *
     * @param int $defaultStoragePid
     * @param array $foreignLocationRecord
     */
    protected function updateStoragePidFromForeignLocationRecord(int &$defaultStoragePid, array $foreignLocationRecord)
    {
        if (
            array_key_exists('pid', $foreignLocationRecord)
            && MathUtility::canBeInterpretedAsInteger($foreignLocationRecord['pid'])
        ) {
            $defaultStoragePid = (int)$foreignLocationRecord['pid'];
        }
    }

    /**
     * Update default storage PID with value/configuration of Maps2 Registry
     *
     * @param int $defaultStoragePid
     * @param array $options
     * @param array $foreignLocationRecord The foreign record with tx_maps2_uid column
     */
    protected function updateStoragePidFromMaps2Registry(int &$defaultStoragePid, array $options, $foreignLocationRecord)
    {
        if (array_key_exists('defaultStoragePid', $options)) {
            $storagePid = $this->getHardCodedStoragePidFromMaps2Registry($options);
            if (empty($storagePid)) {
                $storagePid = $this->getDynamicStoragePidFromMaps2Registry($options, $foreignLocationRecord);
            }

            if (empty($storagePid)) {
                $this->messageHelper->addFlashMessage(
                    'You have configured a defaultStoragePid in maps2 registration, but returned value is still 0. Please check Maps2 Registry',
                    'Invalid defaultStoragePid configuration found',
                    FlashMessage::WARNING
                );
            } else {
                $defaultStoragePid = $storagePid;
            }
        }
    }

    /**
     * Get hard-coded storage PID from Maps2 Registry.
     * Very bad idea, because default storage PID was hard-coded in foreign extension. You should always try to avoid
     * this way and use the dynamic variant instead.
     *
     * @param array $options
     * @return int
     */
    protected function getHardCodedStoragePidFromMaps2Registry(array $options)
    {
        // Very bad idea, as PID will be hardcoded in foreign extension source code
        if (
            !is_array($options['defaultStoragePid'])
            && MathUtility::canBeInterpretedAsInteger($options['defaultStoragePid'])
            && (int)$options['defaultStoragePid'] > 0
        ) {
            return (int)$options['defaultStoragePid'];
        }
        return 0;
    }

    /**
     * Get dynamic storage PID from Maps2 Registry.
     * A way better idea as getHardCodedStoragePidFromMaps2Registry, as that way we read storage PID dynamically from
     * foreign extension configuration ext_conf_template.txt.
     *
     * @param array $options
     * @param array $foreignLocationRecord The foreign record with tx_maps2_uid column
     * @return int
     */
    protected function getDynamicStoragePidFromMaps2Registry(array $options, $foreignLocationRecord): int
    {
        if (is_array($options['defaultStoragePid'])) {
            $hasSubConfiguration = true;
            foreach ($options['defaultStoragePid'] as $configuration) {
                if (!is_array($configuration)) {
                    $hasSubConfiguration = false;
                    break;
                }
            }

            if ($hasSubConfiguration) {
                $defaultStoragePid = 0;
                foreach ($options['defaultStoragePid'] as $configuration) {
                    if (empty($defaultStoragePid)) {
                        $defaultStoragePid = $this->getDynamicStoragePidBySingleArray(
                            $configuration,
                            $foreignLocationRecord
                        );
                    }
                }
                return $defaultStoragePid;
            } else {
                return $this->getDynamicStoragePidBySingleArray($options['defaultStoragePid'], $foreignLocationRecord);
            }
        }

        return 0;
    }


    /**
     * Get dynamic storage PID from a single Maps2 Registry configuration.
     *
     * @param array $configuration
     * @param array $foreignLocationRecord The foreign record with tx_maps2_uid column
     * @return int
     */
    protected function getDynamicStoragePidBySingleArray(array $configuration, $foreignLocationRecord): int
    {
        $defaultStoragePid = 0;
        if (
            array_key_exists('extKey', $configuration)
            && !empty($configuration['extKey'])
            && array_key_exists('property', $configuration)
            && !empty($configuration['property'])
        ) {
            $type = 'extensionmanager';
            if (
                array_key_exists('type', $configuration)
                && !empty($configuration['type'])
                && in_array(strtolower($configuration['type']), ['extensionmanager', 'pagetsconfig'])
            ) {
                $type = strtolower($configuration['type']);
            }

            $extKey = $configuration['extKey'];
            $property = $configuration['property'];

            switch ($type) {
                case 'extensionmanager':
                    if (
                        ExtensionManagementUtility::isLoaded($configuration['extKey'])
                        && array_key_exists($configuration['extKey'], $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'])
                    ) {
                        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);
                        if (
                            MathUtility::canBeInterpretedAsInteger($extConf[$property])
                            && (int)$extConf[$property] > 0
                        ) {
                            return (int)$extConf[$property];
                        }
                    }
                    break;
                default:
                case 'pagetsconfig':
                    $this->updateDefaultStoragePidFromPageTsConfig(
                        $defaultStoragePid,
                        $foreignLocationRecord,
                        $extKey,
                        $property
                    );
            }
        }

        return $defaultStoragePid;
    }

    /**
     * Update default storage PID with value from pageTSconfig
     *
     * @param int $defaultStoragePid
     * @param array $foreignLocationRecord
     * @param string $extKey
     * @param string $property
     */
    protected function updateDefaultStoragePidFromPageTsConfig(int &$defaultStoragePid, array $foreignLocationRecord, string $extKey = 'maps2', string $property = 'defaultStoragePid')
    {
        $tsConfig = $this->getTsConfig($foreignLocationRecord, $extKey);
        if (
            array_key_exists($property, $tsConfig)
            && MathUtility::canBeInterpretedAsInteger($tsConfig[$property])
            && (int)$tsConfig[$property] > 0
        ) {
            $defaultStoragePid = (int)$tsConfig[$property];
        }
    }

    /**
     * Get pageTSconfig for given extension key (ext.ext_key.*)
     *
     * @param array $locationRecord
     * @param string $extKey
     * @return array
     * @throws \Exception
     */
    public function getTsConfig(array $locationRecord, $extKey = 'maps2'): array
    {
        if (
            array_key_exists('pid', $locationRecord)
            && MathUtility::canBeInterpretedAsInteger($locationRecord['pid'])
        ) {
            $pageTsConfig = BackendUtility::getPagesTSconfig($locationRecord['pid']);
            if (
                array_key_exists('ext.', $pageTsConfig)
                && is_array($pageTsConfig['ext.'])
                && array_key_exists($extKey . '.', $pageTsConfig['ext.'])
                && is_array($pageTsConfig['ext.'][$extKey . '.'])
            ) {
                return $pageTsConfig['ext.'][$extKey . '.'];
            }
        }
        return [];
    }
}

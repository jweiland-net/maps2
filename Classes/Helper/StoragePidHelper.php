<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Helper;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * This class searches various places to find a storage PID where new POI Collections should be saved
 */
class StoragePidHelper
{
    protected MessageHelper $messageHelper;

    public function __construct(MessageHelper $messageHelper = null)
    {
        $this->messageHelper = $messageHelper ?? GeneralUtility::makeInstance(MessageHelper::class);
    }

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
     */
    protected function updateStoragePidFromForeignLocationRecord(
        int &$defaultStoragePid,
        array $foreignLocationRecord
    ): void {
        if (!array_key_exists('pid', $foreignLocationRecord)) {
            return;
        }

        if (!MathUtility::canBeInterpretedAsInteger($foreignLocationRecord['pid'])) {
            return;
        }

        $defaultStoragePid = (int)$foreignLocationRecord['pid'];
    }

    /**
     * Update default storage PID with value/configuration of Maps2 Registry
     */
    protected function updateStoragePidFromMaps2Registry(
        int &$defaultStoragePid,
        array $options, $foreignLocationRecord
    ): void {
        if (array_key_exists('defaultStoragePid', $options)) {
            $storagePid = $this->getHardCodedStoragePidFromMaps2Registry($options);
            if (empty($storagePid)) {
                $storagePid = $this->getDynamicStoragePidFromMaps2Registry($options, $foreignLocationRecord);
            }

            if (empty($storagePid)) {
                $this->messageHelper->addFlashMessage(
                    'You have configured a defaultStoragePid in maps2 registration, but returned value is still 0. Please check Maps2 Registry',
                    'Invalid defaultStoragePid configuration found',
                    AbstractMessage::WARNING
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
     */
    protected function getHardCodedStoragePidFromMaps2Registry(array $options): int
    {
        if (is_array($options['defaultStoragePid'])) {
            return 0;
        }

        if (!MathUtility::canBeInterpretedAsInteger($options['defaultStoragePid'])) {
            return 0;
        }

        if ((int)$options['defaultStoragePid'] <= 0) {
            return 0;
        }

        return (int)$options['defaultStoragePid'];
    }

    /**
     * Get dynamic storage PID from Maps2 Registry.
     * A way better idea as getHardCodedStoragePidFromMaps2Registry, as that way we read storage PID dynamically from
     * foreign extension configuration ext_conf_template.txt.
     */
    protected function getDynamicStoragePidFromMaps2Registry(array $options, array $foreignLocationRecord): int
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
            }

            return $this->getDynamicStoragePidBySingleArray($options['defaultStoragePid'], $foreignLocationRecord);
        }

        return 0;
    }

    /**
     * Get dynamic storage PID from a single Maps2 Registry configuration.
     */
    protected function getDynamicStoragePidBySingleArray(array $configuration, array $foreignLocationRecord): int
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
                    if (!ExtensionManagementUtility::isLoaded($configuration['extKey'])) {
                        return $defaultStoragePid;
                    }

                    try {
                        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
                        $extConf = (array)$extensionConfiguration->get($extKey);
                    } catch (\Exception $exception) {
                        return $defaultStoragePid;
                    }

                    if (
                        array_key_exists($property, $extConf)
                        && MathUtility::canBeInterpretedAsInteger($extConf[$property])
                        && (int)$extConf[$property] > 0
                    ) {
                        return (int)$extConf[$property];
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
     */
    protected function updateDefaultStoragePidFromPageTsConfig(
        int &$defaultStoragePid,
        array $foreignLocationRecord,
        string $extKey = 'maps2',
        string $property = 'defaultStoragePid'
    ): void {
        $tsConfig = $this->getTsConfig($foreignLocationRecord, $extKey);
        if (!array_key_exists($property, $tsConfig)) {
            return;
        }

        if (!MathUtility::canBeInterpretedAsInteger($tsConfig[$property])) {
            return;
        }

        if ((int)$tsConfig[$property] <= 0) {
            return;
        }

        $defaultStoragePid = (int)$tsConfig[$property];
    }

    /**
     * Get pageTSconfig for given extension key (ext.ext_key.*)
     *
     * @throws \Exception
     * @return mixed[]
     */
    public function getTsConfig(array $locationRecord, string $extKey = 'maps2'): array
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

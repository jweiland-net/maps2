<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Helper;

use JWeiland\Maps2\Tca\ColumnRegistration;
use JWeiland\Maps2\Tca\StoragePidLocation;
use JWeiland\Maps2\Tca\StoragePidLocationTypeEnum;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * This class searches various places to find a storage PID where new POI Collections should be saved
 */
readonly class StoragePidHelper
{
    public function __construct(
        protected MessageHelper $messageHelper,
        protected ExtensionConfiguration $extensionConfiguration,
    ) {}

    public function getDefaultStoragePidForNewPoiCollection(
        array $foreignLocationRecord,
        ColumnRegistration $columnRegistration,
    ): int {
        $defaultStoragePid = 0;

        $this->updateStoragePidFromForeignLocationRecord($defaultStoragePid, $foreignLocationRecord);
        $this->updateStoragePidFromMaps2Registry($defaultStoragePid, $columnRegistration, $foreignLocationRecord);
        $this->updateDefaultStoragePidFromPageTsConfig(
            $defaultStoragePid,
            $foreignLocationRecord,
            new StoragePidLocation(
                'maps2',
                StoragePidLocationTypeEnum::PAGE_TS_CONFIG,
                'defaultStoragePid',
            )
        );

        if ($defaultStoragePid === 0) {
            $this->messageHelper->addFlashMessage(
                'No PID found to store POI collection. Please check various places like pageTSconfig, '
                . 'Maps2 Registry and PID of this currently saved record.',
                'Can not find a valid PID to store EXT:maps2 records',
            );
        }

        return $defaultStoragePid;
    }

    /**
     * Lowest priority:
     * Get a default location record from a foreign location record
     */
    protected function updateStoragePidFromForeignLocationRecord(
        int &$defaultStoragePid,
        array $foreignLocationRecord,
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
     * Update the default storage PID with the value / configuration of Maps2 Registry
     */
    protected function updateStoragePidFromMaps2Registry(
        int &$defaultStoragePid,
        ColumnRegistration $columnRegistration,
        array $foreignLocationRecord,
    ): void {
        if (!$columnRegistration->hasDefaultStoragePid()) {
            return;
        }

        $storagePid = $this->getHardCodedStoragePidFromMaps2Registry($columnRegistration);
        if ($storagePid === 0) {
            $storagePid = $this->getDynamicStoragePidFromMaps2Registry($columnRegistration, $foreignLocationRecord);
        }

        if ($storagePid === 0) {
            $this->messageHelper->addFlashMessage(
                'You have configured a defaultStoragePid in maps2 registration, but returned value '
                . 'is still 0. Please check Maps2 Registry',
                'Invalid defaultStoragePid configuration found',
                ContextualFeedbackSeverity::WARNING,
            );
        } else {
            $defaultStoragePid = $storagePid;
        }
    }

    /**
     * Get hard-coded storage PID from Maps2 Registry.
     * Very bad idea, because default storage PID was hard-coded in a foreign extension. You should always try to avoid
     * this way and use the dynamic variant instead.
     */
    protected function getHardCodedStoragePidFromMaps2Registry(ColumnRegistration $columnRegistration): int
    {
        if (is_array($columnRegistration->getDefaultStoragePid())) {
            return 0;
        }

        return $columnRegistration->getDefaultStoragePid();
    }

    /**
     * Get dynamic storage PID from Maps2 Registry.
     * A way better idea as getHardCodedStoragePidFromMaps2Registry, as that way we read storage PID dynamically from
     * foreign extension configuration ext_conf_template.txt.
     */
    protected function getDynamicStoragePidFromMaps2Registry(
        ColumnRegistration $columnRegistry,
        array $foreignLocationRecord,
    ): int {
        if (!is_array($columnRegistry->getDefaultStoragePid())) {
            return 0;
        }

        $defaultStoragePid = 0;
        foreach ($columnRegistry->getDefaultStoragePid() as $configuration) {
            switch ($configuration->getType()) {
                case StoragePidLocationTypeEnum::EXTENSION_MANAGER:
                    if (!ExtensionManagementUtility::isLoaded($configuration->getExtKey())) {
                        return $defaultStoragePid;
                    }

                    try {
                        $extConf = (array)$this->extensionConfiguration->get($configuration->getExtKey());
                    } catch (\Exception) {
                        return $defaultStoragePid;
                    }

                    if (
                        array_key_exists($configuration->getProperty(), $extConf)
                        && MathUtility::canBeInterpretedAsInteger($extConf[$configuration->getProperty()])
                        && (int)$extConf[$configuration->getProperty()] > 0
                    ) {
                        return (int)$extConf[$configuration->getProperty()];
                    }
                    break;
                default:
                case StoragePidLocationTypeEnum::PAGE_TS_CONFIG:
                    $this->updateDefaultStoragePidFromPageTsConfig(
                        $defaultStoragePid,
                        $foreignLocationRecord,
                        $configuration,
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
        StoragePidLocation $configuration,
    ): void {
        $tsConfig = $this->getTsConfig($foreignLocationRecord, $configuration->getExtKey());
        if (!array_key_exists($configuration->getProperty(), $tsConfig)) {
            return;
        }

        if (!MathUtility::canBeInterpretedAsInteger($tsConfig[$configuration->getProperty()])) {
            return;
        }

        if ((int)$tsConfig[$configuration->getProperty()] <= 0) {
            return;
        }

        $defaultStoragePid = (int)$tsConfig[$configuration->getProperty()];
    }

    /**
     * Get pageTSconfig for the given extension key (ext.ext_key.*)
     *
     * @throws \Exception
     */
    protected function getTsConfig(array $locationRecord, string $extKey = 'maps2'): array
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

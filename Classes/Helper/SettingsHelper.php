<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Helper;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Helper to prepare settings
 */
class SettingsHelper
{
    protected ConfigurationManagerInterface $configurationManager;

    public function __construct(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * If possible you should always set $settings. In context of controllers $settings contains a merged version
     * of TS settings and FlexForm settings. If you don't have any settings by hand, leave empty, and we will
     * try to get settings from TypoScript (no FlexForm settings!!!)
     */
    public function getPrepareSettings(array $settings = []): array
    {
        $settings = $settings ?: $this->getTypoScriptSettings();

        $settings['forceZoom'] = (bool)($settings['forceZoom'] ?? false);

        // https://wiki.openstreetmap.org/wiki/Tile_servers tolds to use ${x} placeholders, but they don't work.
        if (!empty($settings['mapTile'])) {
            $settings['mapTile'] = str_replace(
                ['${s}', '${x}', '${y}', '${z}'],
                ['{s}', '{x}', '{y}', '{z}'],
                $settings['mapTile']
            );
        }

        if (
            isset(
                $settings['markerClusterer']['enable'],
                $settings['markerClusterer']['imagePath']
            )
            && !empty($settings['markerClusterer']['enable'])
            && !empty($settings['markerClusterer']['imagePath'])
        ) {
            $settings['markerClusterer']['enable'] = 1;
            $settings['markerClusterer']['imagePath'] = PathUtility::getAbsoluteWebPath(
                GeneralUtility::getFileAbsFileName(
                    $settings['markerClusterer']['imagePath']
                )
            );
        }

        return $settings;
    }

    protected function getTypoScriptSettings(): array
    {
        return $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'Maps2',
            'Maps2'
        ) ?? [];
    }
}

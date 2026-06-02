<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Helper;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Helper to prepare settings
 */
readonly class SettingsHelper
{
    public function __construct(
        protected TypoScriptService $typoScriptService,
    ) {}

    /**
     * This method will merge TypoScript and FlexForm settings of EXT:maps2 and should be called
     * by maps2 only.
     *
     * Extbase merges TypoScript and FlexForm settings in a way where empty FlexForm values overwrite
     * TypoScript defaults. To preserve TypoScript defaults, this method retrieves the original,
     * untouched TypoScript settings separately and restores those values when the merged setting
     * contains an empty string.
     *
     * Be careful using this method from within foreign extensions. The context may differ. It may happen
     * that FlexForm settings of your plugin will be merged with TypoScript settings of maps2. This can
     * lead to unforeseen miss-configuration.
     */
    public function restoreTypoScriptDefaultsForEmptyFlexFormSettings(
        array $mergedSettingsFromController,
        ServerRequestInterface $request,
    ): array {
        $pluginConfiguration = $this->getPluginConfiguration($request);
        $pluginSettings = $pluginConfiguration['settings'] ?? [];

        foreach ($mergedSettingsFromController as $mergedSetting => $value) {
            if ($value === '' && isset($pluginSettings[$mergedSetting])) {
                $mergedSettingsFromController[$mergedSetting] = $pluginSettings[$mergedSetting];
            }
        }

        return $mergedSettingsFromController ?: $pluginSettings;
    }

    public function getPreparedSettings(array $settings, ServerRequestInterface $request): array
    {
        $settings['forceZoom'] = (bool)($settings['forceZoom'] ?? false);

        $this->prepareMapTileForOpenStreetMap($settings);
        $this->prepareImagePathForMarkerClusterer($settings, $request);

        return $settings;
    }

    protected function prepareMapTileForOpenStreetMap(array &$settings): void
    {
        // https://wiki.openstreetmap.org/wiki/Tile_servers told you to use ${x} placeholders, but they don't work.
        if (!empty($settings['mapTile'])) {
            $settings['mapTile'] = str_replace(
                ['${s}', '${x}', '${y}', '${z}'],
                ['{s}', '{x}', '{y}', '{z}'],
                $settings['mapTile'],
            );
        }
    }

    protected function prepareImagePathForMarkerClusterer(array &$settings, ServerRequestInterface $request): void
    {
        if (
            isset($settings['markerClusterer']['enable'], $settings['markerClusterer']['imagePath'])
            && (string)$settings['markerClusterer']['enable'] === '1'
        ) {
            $settings['markerClusterer']['enable'] = 1;

            $imageWebPath = PathUtility::getAbsoluteWebPath(
                GeneralUtility::getFileAbsFileName($settings['markerClusterer']['imagePath']),
            );

            $settings['markerClusterer']['imagePath'] = GeneralUtility::locationHeaderUrl($imageWebPath, $request);
        }
    }

    /**
     * Returns the TypoScript configuration found in plugin.tx_maps2.
     */
    private function getPluginConfiguration(ServerRequestInterface $request): array
    {
        $setup = $this->getTypoScriptSetup($request);

        $pluginConfiguration = [];

        if (isset($setup['plugin.']['tx_maps2.']) && is_array($setup['plugin.']['tx_maps2.'])) {
            $pluginConfiguration = $this->typoScriptService->convertTypoScriptArrayToPlainArray($setup['plugin.']['tx_maps2.']);
        }

        return $pluginConfiguration;
    }

    protected function getTypoScriptSetup(ServerRequestInterface $request): array
    {
        $frontendTypoScript = $request->getAttribute('frontend.typoscript');
        if (!($frontendTypoScript instanceof FrontendTypoScript)) {
            throw new \RuntimeException(
                'Setup array has not been initialized. This happens in cached Frontend scope where full TypoScript'
                . ' is not needed by the system.',
                1780395099,
            );
        }
        return $frontendTypoScript->getSetupArray();
    }
}

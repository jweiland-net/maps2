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
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\Publishing\UriGenerationOptions;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Helper to prepare settings
 */
readonly class SettingsHelper
{
    public function __construct(
        private SystemResourceFactory $systemResourceFactory,
        private SystemResourcePublisherInterface $resourcePublisher,
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
    public function getMergedSettings(array $mergedSettingsFromController, ServerRequestInterface $request): array
    {
        $fullTypoScript = $this->getTypoScriptSetup($request);


        $typoScriptSettings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'maps2',
            'invalid', // invalid plugin name to get fresh unmerged settings
        );

        // In context of a maps2 plugin this will return the merged (TS and FlexForm) settings
        $mergedSettings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
        );

        foreach ($mergedSettings as $setting => $value) {
            if ($value === '' && isset($typoScriptSettings['settings'][$setting])) {
                $mergedSettings[$setting] = $typoScriptSettings['settings'][$setting];
            }
        }

        return $mergedSettings;
    }


    /**
     * If possible, you should always set $settings. In the context of controllers $settings contain a
     * merged version of TS settings and FlexForm settings. If you don't have any settings by hand, leave
     * empty, and we will try to get settings from TypoScript (no FlexForm settings!!!)
     */
    public function getPreparedSettings(array $settings = []): array
    {
        $settings = $settings ?: $this->getTypoScriptSettings();

        $settings['forceZoom'] = (bool)($settings['forceZoom'] ?? false);

        $this->prepareMapTileForOpenStreetMap($settings);
        $this->prepareImagePathForMarkerClusterer($settings);

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

    protected function prepareImagePathForMarkerClusterer(array &$settings): void
    {
        if (
            !empty($settings['markerClusterer']['enable'])
            && !empty($settings['markerClusterer']['imagePath'])
        ) {
            $settings['markerClusterer']['enable'] = 1;
            if (method_exists(PathUtility::class, 'getPublicResourceWebPath')) {
                $resource = $this->systemResourceFactory->createPublicResource($settings['markerClusterer']['imagePath']);
                $settings['markerClusterer']['imagePath'] = (string)$this->resourcePublisher->generateUri($resource, $GLOBALS['TYPO3_REQUEST'], new UriGenerationOptions(absoluteUri: true));
            } else {
                $settings['markerClusterer']['imagePath'] = PathUtility::getAbsoluteWebPath(
                    GeneralUtility::getFileAbsFileName(
                        $settings['markerClusterer']['imagePath'],
                    ),
                );
            }
        }
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

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Helper;

use JWeiland\Maps2\Configuration\ExtConf;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Little helper with a very reduced set of dependencies like Extbase. Useful, if you need f.e. the configured
 * MapProvider at a very early state of TYPO3 like Middlewares.
 */
class MapHelper
{
    /**
     * @var ExtConf
     */
    protected ExtConf $extConf;

    public function __construct(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * Get currently valid default map provider
     */
    public function getMapProvider(array $databaseRow = []): string
    {
        $mapProvider = '';

        // Only if both map providers are allowed, we can read map provider from Database
        if ($this->extConf->getMapProvider() === 'both') {
            if (!empty($databaseRow)) {
                $mapProvider = $this->getMapProviderFromDatabase($databaseRow);
            }

            if (empty($mapProvider)) {
                $mapProvider = $this->extConf->getDefaultMapProvider();
            }
        } else {
            // We have a strict map provider.
            $mapProvider = $this->extConf->getMapProvider();
        }

        return $mapProvider;
    }

    /**
     * Try to retrieve a default map provider from given database record
     */
    protected function getMapProviderFromDatabase(array $databaseRow): string
    {
        $mapProvider = '';

        if (
            array_key_exists('map_provider', $databaseRow)
            && !empty($databaseRow['map_provider'])
        ) {
            if (is_array($databaseRow['map_provider'])) {
                // We have a record from TCEMAIN
                $mapProvider = (string)current($databaseRow['map_provider']);
            } elseif (is_string($databaseRow['map_provider'])) {
                // We have a normal array based record from database
                $mapProvider = $databaseRow['map_provider'];
            }
        }

        return $mapProvider;
    }

    /**
     * POIs are stored as JSON in tx_maps_domain_model_poicollection.
     * Use this method to convert the JSON back into an array.
     *
     * @param string $poisAsJson That's normally the content of column "configuration_map"
     * @return array<string, string>[]|bool[]
     */
    public function convertPoisAsJsonToArray(string $poisAsJson): array
    {
        $pois = [];

        try {
            foreach (json_decode($poisAsJson, true, 512, JSON_THROW_ON_ERROR) ?? [] as $poi) {
                $pois[] = array_combine(
                    [
                        'latitude',
                        'longitude'
                    ],
                    GeneralUtility::trimExplode(',', $poi)
                );
            }
        } catch (\JsonException $jsonException) {
            // Return empty POIs
        }

        return $pois;
    }

    /**
     * Check, if the current request is allowed to process/show the map in frontend.
     * It respects the settings from Extension Settings.
     * If false, an overlay will be shown instead of the map and no JavaScript files
     * will be loaded for maps2.
     */
    public function isRequestToMapProviderAllowed(): bool
    {
        if ($this->extConf->getExplicitAllowMapProviderRequests()) {
            // Check, if cookie with last consent was available
            if (isset($_COOKIE['mapProviderRequestsAllowedForMaps2'])) {
                return true;
            }

            // Else, check GET parameter for consent
            $parameters = GeneralUtility::_GPmerged('tx_maps2_maps2');
            return isset($parameters['mapProviderRequestsAllowedForMaps2'])
                && (int)$parameters['mapProviderRequestsAllowedForMaps2'] === 1;
        }

        return true;
    }
}

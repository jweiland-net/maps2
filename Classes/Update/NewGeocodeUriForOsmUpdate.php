<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Update;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Somewhere in october 2023 OSM has deprecated/removed the use of addresses as path segment in Geocode URI.
 * This UpgradeWizard migrates extension settings to new URI where address is a query parameter now.
 */
class NewGeocodeUriForOsmUpdate implements UpgradeWizardInterface
{
    private string $oldOsmGeocodeUri = 'https://nominatim.openstreetmap.org/search/%s?format=json&addressdetails=1';

    private string $newOsmGeocodeUri = 'https://nominatim.openstreetmap.org/search?q=%s&format=json&addressdetails=1';

    public function getIdentifier(): string
    {
        return 'maps2NewOsmGeocodeUriExtConf';
    }

    public function getTitle(): string
    {
        return '[maps2] Migrate to new OSM Geocode URI in extension settings';
    }

    public function getDescription(): string
    {
        return 'OpenStreetMap has changed its Geocoding URI. The address has to be set as additional query parameter' .
            'now. Adding the address as path segment seems to be removed somewhere in October 2023.';
    }

    public function updateNecessary(): bool
    {
        return $this->getOsmGeocodeUri() === $this->oldOsmGeocodeUri;
    }

    public function executeUpdate(): bool
    {
        if ($this->getOsmGeocodeUri() === $this->oldOsmGeocodeUri) {
            try {
                $maps2ExtensionConfiguration = $this->getExtensionConfiguration()->get('maps2');
                if (
                    is_array($maps2ExtensionConfiguration)
                    && array_key_exists('openStreetMapGeocodeUri', $maps2ExtensionConfiguration)
                ) {
                    $maps2ExtensionConfiguration['openStreetMapGeocodeUri'] = $this->newOsmGeocodeUri;
                }
                $this->getExtensionConfiguration()->set('maps2', $maps2ExtensionConfiguration);

                return true;
            } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException $e) {
            }
        }

        return false;
    }

    private function getOsmGeocodeUri(): string
    {
        try {
            return $this->getExtensionConfiguration()->get('maps2', 'openStreetMapGeocodeUri');
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException $e) {
            return '';
        }
    }

    private function getExtensionConfiguration(): ExtensionConfiguration
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class);
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}

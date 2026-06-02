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
use TYPO3\CMS\Core\Country\Country;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extract address parts from a foreign record array and build an address for Geocode requests
 */
class AddressHelper
{
    public function __construct(
        protected MessageHelper $messageHelper,
        protected CountryProvider $countryProvider,
        protected ExtConf $extConf,
    ) {}

    /**
     * Get address for Map Providers GeoCode requests
     */
    public function getAddress(array $locationRecordToSave, array $options): string
    {
        if (!$this->isValidOptionConfiguration($options)) {
            return '';
        }

        $this->unifyOptionConfiguration($options);
        $locationRecordToSave = array_map(
            static fn($value) => is_string($value) ? trim($value) : $value,
            $locationRecordToSave,
        );

        $addressParts = [];
        foreach ($options['addressColumns'] as $addressColumn) {
            if (!empty($locationRecordToSave[$addressColumn])) {
                $addressParts[] = $locationRecordToSave[$addressColumn];
            }
        }

        $addressParts[] = $this->getCountryName($locationRecordToSave, $options);

        return trim(implode(' ', $addressParts));
    }

    /**
     * Check if a formatted address is still equal with the address parts of a foreign location record.
     */
    public function isSameAddress(string $address, array $foreignLocationRecord, array $options): bool
    {
        // Convert formatted address like "Mainstreet 15, 51324 Cologne, Germany" into array
        $poiCollectionAddressParts = GeneralUtility::trimExplode(
            ' ',
            str_replace(',', '', strtolower($address)),
        );
        foreach ($options['addressColumns'] as $addressColumn) {
            if (in_array(
                strtolower((string)$foreignLocationRecord[$addressColumn]),
                $poiCollectionAddressParts,
                true,
            )) {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * Try to get a country name from a foreign extension record.
     * If we do not find a country name, we will try some fallbacks.
     */
    protected function getCountryName(array $record, array $options): string
    {
        $defaultCountry = $this->getFallbackCountryName($options);

        $countryColumn = $options['countryColumn'] ?? '';
        if ($countryColumn === '') {
            return $defaultCountry;
        }

        $countryValue = $record[$countryColumn] ?? '';
        if ($countryValue === '' || $countryValue === '0') {
            return $defaultCountry;
        }

        $country = $this->countryProvider->getByIsoCode($countryValue);
        if ($country instanceof Country) {
            return $country->getName();
        }

        $country = $this->countryProvider->getByEnglishName($countryValue);
        if ($country instanceof Country) {
            return $country->getName();
        }

        return $defaultCountry;
    }

    /**
     * If we cannot get any country information of foreign extension,
     * we now try some fallbacks to get a country name.
     */
    protected function getFallbackCountryName(array $options): string
    {
        // try to get defaultCountry from maps2 registry
        if (array_key_exists('defaultCountry', $options) && !empty($options['defaultCountry'])) {
            return trim((string)$options['defaultCountry']);
        }

        $defaultCountry = $this->extConf->getDefaultCountry();
        if ($defaultCountry !== '' && $defaultCountry !== '0') {
            return trim($defaultCountry);
        }

        return '';
    }

    /**
     * Unify option configuration
     */
    protected function unifyOptionConfiguration(array &$options): void
    {
        // unify addressColumns
        if (is_string($options['addressColumns'])) {
            $options['addressColumns'] = GeneralUtility::trimExplode(',', $options['addressColumns']);
        } else {
            array_map(trim(...), $options['addressColumns']);
        }

        // unify countryColumn
        $options['countryColumn'] = array_key_exists('countryColumn', $options)
            ? trim((string)$options['countryColumn'])
            : '';

        // remove countryColumn from addressColumns
        if (($options['countryColumn'] !== '' && $options['countryColumn'] !== '0')) {
            $key = array_search($options['countryColumn'], $options['addressColumns']);
            if ($key) {
                unset($options['addressColumns'][$key]);
            }
        }
    }

    /**
     * Check if configured options are valid
     */
    protected function isValidOptionConfiguration(array $options): bool
    {
        if (!array_key_exists('addressColumns', $options)) {
            $this->messageHelper->addFlashMessage(
                'Array key "addressColumns" does not exist in your maps2 registration. This field must be filled to prevent creating empty Geocode requests.',
                'Key addressColumns is missing',
                ContextualFeedbackSeverity::ERROR,
            );
            return false;
        }

        if (empty($options['addressColumns'])) {
            $this->messageHelper->addFlashMessage(
                'Array key "addressColumns" is a required field in maps2 registration. Please fill it with column names of your table.',
                'Key addressColumns is empty',
                ContextualFeedbackSeverity::ERROR,
            );
            return false;
        }

        return true;
    }
}

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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Extract address parts from foreign record array and build an address for Google Maps GoeCode requests
 */
class AddressHelper
{
    protected MessageHelper $messageHelper;

    public function __construct(MessageHelper $messageHelper)
    {
        $this->messageHelper = $messageHelper;
    }

    /**
     * Get address for Map Providers GeoCode requests
     */
    public function getAddress(array $locationRecordToSave, array $options): string
    {
        if (!$this->isValidOptionConfiguration($options)) {
            return '';
        }

        $this->unifyOptionConfiguration($options);
        $locationRecordToSave = array_map(static function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $locationRecordToSave);

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
     * Check, if a formatted address is still equal with the address parts of foreign location record.
     */
    public function isSameAddress(string $address, array $foreignLocationRecord, array $options): bool
    {
        // Convert formatted address like "Mainstreet 15, 51324 Cologne, Germany" into array
        $poiCollectionAddressParts = GeneralUtility::trimExplode(
            ' ',
            str_replace(',', '', strtolower($address))
        );
        foreach ($options['addressColumns'] as $addressColumn) {
            if (in_array(
                strtolower($foreignLocationRecord[$addressColumn]),
                $poiCollectionAddressParts,
                true
            )) {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * Try to get a country name from foreign extension record.
     * If we do not find a country name, we will try some fallbacks.
     */
    protected function getCountryName(array $record, array $options): string
    {
        if ($this->canCountryBeLoadedFromStaticCountry($record, $options['countryColumn'])) {
            $countryName = $this->getCountryNameFromStaticCountries((int)$record[$options['countryColumn']]);
        } elseif (array_key_exists($options['countryColumn'], $record)) {
            $countryName = $record[$options['countryColumn']];
        } else {
            $countryName = $this->getFallbackCountryName($options);
        }

        return $countryName;
    }

    /**
     * If we can not get any country information of foreign extension,
     * we now try some fallbacks to get a country name.
     */
    protected function getFallbackCountryName(array $options): string
    {
        // try to get defaultCountry from maps2 registry
        if (array_key_exists('defaultCountry', $options) && !empty($options['defaultCountry'])) {
            return trim($options['defaultCountry']);
        }

        $this->messageHelper->addFlashMessage(
            'We can not find any country information within your extension. Either in Maps2 Registry nor in this record. Please check your configuration or update your extension.',
            'No country information found',
            AbstractMessage::WARNING
        );

        // try to get default country of maps2 extConf
        $extConf = GeneralUtility::makeInstance(ExtConf::class);
        $defaultCountry = $extConf->getDefaultCountry();
        if ($defaultCountry) {
            return trim($defaultCountry);
        }

        $this->messageHelper->addFlashMessage(
            'Default country in maps2 of extension manager configuration is empty. Request to Google Maps GeoCode will start without any country information, which may lead to curious results.',
            'Default country of maps2 is not configured',
            AbstractMessage::WARNING
        );

        return '';
    }

    protected function getCountryNameFromStaticCountries(int $uid): string
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('static_countries');
        $countryRecord = $queryBuilder
            ->select('cn_short_en')
            ->from('static_countries')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        if (empty($countryRecord)) {
            $this->messageHelper->addFlashMessage(
                'Country with UID "' . $uid . '" could not be found in static_countries table. Please check your record for correct country field.',
                'Country not found in DB',
                AbstractMessage::WARNING
            );

            return '';
        }

        return $countryRecord['cn_short_en'];
    }

    /**
     * Check, if we can load country name from static_countries
     */
    protected function canCountryBeLoadedFromStaticCountry(array $record, string $countryColumn): bool
    {
        if (empty($countryColumn)) {
            return false;
        }

        if (!array_key_exists($countryColumn, $record)) {
            return false;
        }

        if (!MathUtility::canBeInterpretedAsInteger($record[$countryColumn])) {
            return false;
        }

        return ExtensionManagementUtility::isLoaded('static_info_tables');
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
            array_map('trim', $options['addressColumns']);
        }

        // unify countryColumn
        $options['countryColumn'] = array_key_exists('countryColumn', $options) ? trim($options['countryColumn']) : '';

        // remove countryColumn from addressColumns
        if (!empty($options['countryColumn'])) {
            $key = array_search($options['countryColumn'], $options['addressColumns']);
            if ($key) {
                unset($options['addressColumns'][$key]);
            }
        }
    }

    /**
     * Check, if configured options are valid
     */
    protected function isValidOptionConfiguration(array $options): bool
    {
        if (!array_key_exists('addressColumns', $options)) {
            $this->messageHelper->addFlashMessage(
                'Array key "addressColumns" does not exist in your maps2 registration. This field must be filled to prevent creating empty GeoCode requests to google.',
                'Key addressColumns is missing',
                AbstractMessage::ERROR
            );
            return false;
        }

        if (empty($options['addressColumns'])) {
            $this->messageHelper->addFlashMessage(
                'Array key "addressColumns" is a required field in maps2 registraton. Please fill it with column names of your table.',
                'Key addressColumns is empty',
                AbstractMessage::ERROR
            );
            return false;
        }

        return true;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}

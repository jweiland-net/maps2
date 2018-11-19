<?php

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

use JWeiland\Maps2\Configuration\ExtConf;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Extract address parts from foreign record array and build an address for Google Maps GoeCode requests
 */
class AddressHelper
{
    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    /**
     * AddressHelper constructor.
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
     * Get address for Google Maps GeoCode requests
     *
     * @param array $locationRecordToSave
     * @param array $options
     * @return string Prepared address for Google requests
     */
    public function getAddress(array $locationRecordToSave, array $options): string
    {
        if (!$this->isValidOptionConfiguration($options)) {
            return '';
        }

        $this->unifyOptionConfiguration($options);
        $locationRecordToSave = array_map('trim', $locationRecordToSave);

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
     * Try to get a country name from foreign extension record.
     * If we do not find a country name, we will try some fallbacks.
     *
     * @param array $record The record to search for country information
     * @param array $options The options from maps2 registry
     * @return string
     */
    protected function getCountryName(array $record, array $options): string
    {
        if ($this->canCountryBeLoadedFromStaticCountry($record, $options['countryColumn'])) {
            $countryName = $this->getCountryNameFromStaticCountries($record[$options['countryColumn']]);
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
     *
     * @param array $options The options from maps2 registry
     * @return string
     */
    protected function getFallbackCountryName(array $options)
    {
        // try to get defaultCountry from maps2 registry
        if (array_key_exists('defaultCountry', $options) && !empty($options['defaultCountry'])) {
            return trim($options['defaultCountry']);
        } else {
            $this->messageHelper->addFlashMessage(
                'We can not find any country information within your extension. Either in Maps2 Registry nor in this record. Please check your configuration or update your extension.',
                'No country information found',
                FlashMessage::WARNING
            );
        }

        // try to get default country of maps2 extConf
        $extConf = GeneralUtility::makeInstance(ExtConf::class);
        $defaultCountry = $extConf->getDefaultCountry();
        if ($defaultCountry) {
            return trim($defaultCountry);
        }

        $this->messageHelper->addFlashMessage(
            'Default country in maps2 of extension manager configuration is empty. Request to Google Maps GeoCode will start without any country information, which may lead to curious results.',
            'Default country of maps2 is not configured',
            FlashMessage::WARNING
        );

        return '';
    }

    /**
     * Get country name from static_countries table
     *
     * @param int $uid
     * @return string
     */
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
                FlashMessage::WARNING
            );
            return '';
        } else {
            return $countryRecord['cn_short_en'];
        }
    }

    /**
     * Check, if we can load country name from static_countries
     *
     * @param array $record
     * @param string $countryColumn
     * @return bool
     */
    protected function canCountryBeLoadedFromStaticCountry(array $record, string $countryColumn)
    {
        return !empty($countryColumn)
            && array_key_exists($countryColumn, $record)
            && MathUtility::canBeInterpretedAsInteger($record[$countryColumn])
            && ExtensionManagementUtility::isLoaded('static_info_tables');
    }

    /**
     * Unify option configuration
     *
     * @param array $options Options to unify
     */
    protected function unifyOptionConfiguration(array &$options)
    {
        // unify addressColumns
        if (is_string($options['addressColumns'])) {
            $options['addressColumns'] = GeneralUtility::trimExplode(',', $options['addressColumns']);
        } else {
            array_map('trim', $options['addressColumns']);
        }

        // unify countryColumn
        if (!array_key_exists('countryColumn', $options)) {
            $options['countryColumn'] = '';
        } else {
            $options['countryColumn'] = trim($options['countryColumn']);
        }

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
     *
     * @param array $options
     * @return bool
     */
    protected function isValidOptionConfiguration(array $options): bool
    {
        if (!array_key_exists('addressColumns', $options)) {
            $this->messageHelper->addFlashMessage(
                'Array key "addressColumns" does not exist in your maps2 registration. This field must be filled to prevent creating empty GeoCode requests to google.',
                'Key addressColumns is missing',
                FlashMessage::ERROR
            );
            return false;
        }

        if (empty($options['addressColumns'])) {
            $this->messageHelper->addFlashMessage(
                'Array key "addressColumns" is a required field in maps2 registraton. Please fill it with column names of your table.',
                'Key addressColumns is empty',
                FlashMessage::ERROR
            );
            return false;
        }

        return true;
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool()
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Mapper;

use JWeiland\Maps2\Domain\Model\Position;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Map Open Street Map Request into Position object
 */
class OpenStreetMapMapper implements MapperInterface
{
    public function map(array $response): ObjectStorage
    {
        $objectStorage = GeneralUtility::makeInstance(ObjectStorage::class);
        foreach ($response as $data) {
            $objectStorage->attach($this->getPosition($data));
        }

        return $objectStorage;
    }

    protected function getPosition(array $data): Position
    {
        $position = GeneralUtility::makeInstance(Position::class);
        $position->setFormattedAddress($this->getFormattedAddress($data));
        $position->setLatitude((float)$data['lat'] ?? 0.0);
        $position->setLongitude((float)$data['lon'] ?? 0.0);

        return $position;
    }

    /**
     * Extract address parts from Response to build a formatted address
     */
    protected function getFormattedAddress(array $data): string
    {
        if (array_key_exists('address', $data)) {
            $data['address']['city'] = $this->getCityFromAddress($data['address']);

            $formattedAddress = sprintf(
                '%s %s, %s %s, %s',
                $data['address']['road'] ?? $data['address']['footway'] ?? '',
                $data['address']['house_number'] ?? '',
                $data['address']['postcode'] ?? '',
                $data['address']['city'] ?? '',
                $data['address']['country'] ?? ''
            );
        } else {
            // 'display_name' can be a very long name.
            // We hope address key is set above to return a reduced formattedAddress
            $formattedAddress = $data['display_name'];
        }

        return trim($formattedAddress, ' ,\t\n\r\0\x0B');
    }

    /**
     * Open Street Map differs between small and big cities.
     * That's why we have to check each kind of city size to get the city name itself.
     */
    protected function getCityFromAddress(array $address): string
    {
        $sortedCityTypes = ['village', 'town', 'city'];
        $city = '';

        foreach ($sortedCityTypes as $type) {
            if (array_key_exists($type, $address)) {
                $city = $address[$type];
            }
        }

        return $city;
    }
}

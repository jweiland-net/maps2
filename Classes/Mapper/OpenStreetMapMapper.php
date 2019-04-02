<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Mapper;

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

use JWeiland\Maps2\Domain\Model\Position;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Map Open Street Map Request into Position object
 */
class OpenStreetMapMapper implements MapperInterface
{
    /**
     * Map Response of Open Street Map GeoCode API to ObjectStorage
     *
     * @param array $response
     * @return ObjectStorage|Position[]
     */
    public function map(array $response): ObjectStorage
    {
        $objectStorage = GeneralUtility::makeInstance(ObjectStorage::class);
        foreach ($response as $data) {
            $objectStorage->attach($this->getPosition($data));
        }
        return $objectStorage;
    }

    /**
     * Use values from $data to build a new Position Model
     *
     * @param $data
     * @return Position
     */
    protected function getPosition($data): Position
    {
        $position = GeneralUtility::makeInstance(Position::class);
        $position->setFormattedAddress($this->getFormattedAddress($data));
        $position->setLatitude((float)$data['lat'] ?? 0.0);
        $position->setLongitude((float)$data['lon'] ?? 0.0);

        return $position;
    }

    /**
     * Extract address parts from Response to build a formatted address
     *
     * @param array $data
     * @return string
     */
    protected function getFormattedAddress(array $data)
    {
        $formattedAddress = [];

        if (array_key_exists('address', $data)) {
            $sortedAddressTypes = ['road', 'house_number', 'postcode', 'city', 'country'];
            foreach ($sortedAddressTypes as $type) {
                if ($type === 'city') {
                    $formattedAddress[] = $this->getCityFromAddress($data['address']);
                } elseif (array_key_exists($type, $data['address'])) {
                    $formattedAddress[] = $data['address'][$type];
                }
            }
        } else {
            // 'display_name' can be a very long name.
            // We hope address key is set above to return a reduced formattedAddress
            $formattedAddress[] = $data['display_name'];
        }
        return implode(' ', $formattedAddress);
    }

    /**
     * Open Street Map differs between small and big cities.
     * That's why we have to check each kind of city size to get the city name itself.
     *
     * @param array $address
     * @return string
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

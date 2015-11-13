<?php
namespace JWeiland\Maps2\Utility;

/**
 * This file is part of the TYPO3 CMS project.
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Class GoogleMaps
 *
 * @category Utility
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class GoogleMaps
{

    /**
     * @var string
     */
    protected $urlGeocode = 'http://maps.googleapis.com/maps/api/geocode/json?address=|&sensor=false';

    /**
     * @var \JWeiland\Maps2\Utility\DataMapper
     */
    protected $dataMapper;

    /**
     * inject dataMapper
     *
     * @param \JWeiland\Maps2\Utility\DataMapper $dataMapper
     * @return void
     */
    public function injectDataMapper(\JWeiland\Maps2\Utility\DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * find position by address
     *
     * @param string $address
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function findPositionByAddress($address)
    {
        $url = str_replace('|', $this->updateAddressForUri($address), $this->urlGeocode);
        $json = GeneralUtility::getUrl($url);
        $response = json_decode($json, true);
        return $this->dataMapper->mapObjectStorage(
            'JWeiland\\Maps2\\Domain\\Model\\RadiusResult',
            $response['results']
        );
    }

    /**
     * prepare address for an uri
     * further it will add some additional information like country
     *
     * @param string $address The address to update
     * @return string A prepared address which is valid for an uri
     */
    protected function updateAddressForUri($address)
    {
        // check if it can be interpreted as a zip code
        if (MathUtility::canBeInterpretedAsInteger($address) && strlen($address) == 5) {
            $address .= ' Deutschland';
        }
        return rawurlencode($address);
    }
}

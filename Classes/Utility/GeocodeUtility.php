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
use JWeiland\Maps2\Configuration\ExtConf;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class GoogleMaps
 *
 * @category Utility
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class GeocodeUtility
{
    /**
     * @var string
     */
    protected $uriForGeocode = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s';

    /**
     * @var ExtConf
     */
    protected $extConf = null;

    /**
     * @var \JWeiland\Maps2\Utility\DataMapper
     */
    protected $dataMapper;

    /**
     * inject extConf
     *
     * @param \JWeiland\Maps2\Configuration\ExtConf $extConf
     * @return void
     */
    public function injectExtConf(\JWeiland\Maps2\Configuration\ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

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
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage|null
     *
     * @throws \Exception
     */
    public function findPositionByAddress($address)
    {
        $json = GeneralUtility::getUrl($this->getUri($address));
        $response = json_decode($json, true);
        if ($response['status'] === 'OK') {
            return $this->dataMapper->mapObjectStorage(
                'JWeiland\\Maps2\\Domain\\Model\\RadiusResult',
                $response['results']
            );
        } else {
            $message = LocalizationUtility::translate('error.noAddressFound', 'maps2', array(
                $address
            ));
            /** @var $flashMessage \TYPO3\CMS\Core\Messaging\FlashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $message,
                '',
                FlashMessage::WARNING
            );
            /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            /** @var $defaultFlashMessageQueue FlashMessageQueue */
            $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $defaultFlashMessageQueue->enqueue($flashMessage);
            
            if ($GLOBALS['TYPO3_CONF_VARS']['BE']['debug']) {
                DebugUtility::debug($response, 'Response of Google Maps GeoCode API');
            }
    
            return null;
        }
    }

    /**
     * Get URI for Geocode
     *
     * @param string $address
     * @return string
     */
    protected function getUri($address)
    {
        return sprintf(
            $this->uriForGeocode,
            $this->updateAddressForUri($address),
            $this->extConf->getGoogleMapsGeocodeApiKey()
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
        if (MathUtility::canBeInterpretedAsInteger($address) && strlen($address) === 5) {
            $address .= ' Deutschland';
        }
        return rawurlencode($address);
    }
}

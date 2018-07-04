<?php
namespace JWeiland\Maps2\Utility;

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
use JWeiland\Maps2\Domain\Model\RadiusResult;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Send an address to Google API and request Latitude and Longitude
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
    protected $extConf;

    /**
     * @var DataMapper
     */
    protected $dataMapper;

    /**
     * inject extConf
     *
     * @param ExtConf $extConf
     * @return void
     */
    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * inject dataMapper
     *
     * @param \JWeiland\Maps2\Utility\DataMapper $dataMapper
     * @return void
     */
    public function injectDataMapper(DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * find position by address
     *
     * @param string $address
     * @return ObjectStorage|array ObjectStorage if status = OK. Returns array if something went false
     * @throws \Exception
     */
    public function findPositionByAddress($address)
    {
        $json = GeneralUtility::getUrl($this->getUri($address));
        $response = json_decode($json, true);
        if ($response['status'] === 'OK') {
            return $this->dataMapper->mapObjectStorage(RadiusResult::class, $response['results']);
        } else {
            $message = LocalizationUtility::translate('error.noAddressFound', 'maps2', [$address]);
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

            return $response;
        }
    }

    /**
     * Get URI for Geocode
     *
     * @param string $address
     * @return string
     * @throws \Exception
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
        // if address can be interpreted as zip, attach the default country to prevent a worldwide search
        if (
            MathUtility::canBeInterpretedAsInteger($address)
            && !empty($this->extConf->getDefaultCountry())
        ) {
            $address .= ' ' . $this->extConf->getDefaultCountry();
        }

        return rawurlencode($address);
    }
}

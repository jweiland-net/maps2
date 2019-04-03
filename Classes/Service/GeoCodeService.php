<?php
declare(strict_types=1);
namespace JWeiland\Maps2\Service;

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

use JWeiland\Maps2\Client\ClientFactory;
use JWeiland\Maps2\Client\ClientInterface;
use JWeiland\Maps2\Client\Request\RequestFactory;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Mapper\MapperFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * With this class you can start requests to GeoCode API of Map Providers. Search for addresses, assign a POI
 * to a foreign record, save the foreign record and many more. It is designed as an API.
 */
class GeoCodeService implements SingletonInterface
{
    /**
     * Client depends on mapProvider which is either gm or osm
     *
     * @var ClientInterface
     */
    protected $client;

    public function __construct(ClientInterface $client = null)
    {
        if ($client === null) {
            $clientFactory = GeneralUtility::makeInstance(ClientFactory::class);
            $client = $clientFactory->create();
        }
        $this->client = $client;
    }

    /**
     * Get positions by address
     *
     * @param string $address
     * @return ObjectStorage|Position[]
     * @throws \Exception
     * @api
     */
    public function getPositionsByAddress(string $address): ObjectStorage
    {
        $positions = GeneralUtility::makeInstance(ObjectStorage::class);

        // Prevent calls to Map Providers GeoCode API, if address is empty
        if (empty(trim($address))) {
            return $positions;
        }

        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $geocodeRequest = $requestFactory->create('GeocodeRequest');
        $geocodeRequest->addParameter('address', (string)$address);

        $response = $this->client->processRequest($geocodeRequest);
        if (!empty($response)) {
            $mapperFactory = GeneralUtility::makeInstance(MapperFactory::class);
            $positions = $mapperFactory->create()->map($response);
        }

        return $positions;
    }

    /**
     * Get first found position by address
     *
     * @param string $address
     * @return Position|null
     * @throws \Exception
     * @api
     */
    public function getFirstFoundPositionByAddress(string $address)
    {
        $position = null;
        $positions = $this->getPositionsByAddress($address);
        if ($positions->count()) {
            $positions->rewind();
            $position = $positions->current();
        }

        return $position;
    }
}

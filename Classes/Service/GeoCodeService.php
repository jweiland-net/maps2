<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Service;

use JWeiland\Maps2\Client\AbstractClient;
use JWeiland\Maps2\Client\ClientFactory;
use JWeiland\Maps2\Client\ClientInterface;
use JWeiland\Maps2\Client\ClientResult;
use JWeiland\Maps2\Client\Request\RequestFactory;
use JWeiland\Maps2\Client\Request\RequestInterface;
use JWeiland\Maps2\Domain\Model\GeoCodeResult;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Mapper\MapperFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * With this class you can start requests to GeoCode API of Map Providers. Search for addresses, assign a POI
 * to a foreign record, save the foreign record and many more. It is designed as an API.
 */
class GeoCodeService implements SingletonInterface
{
    /**
     * Client depends on mapProvider which is either gm or osm
     */
    protected ClientInterface $client;

    public function __construct(
        protected ClientFactory $clientFactory,
        protected RequestFactory $requestFactory,
        protected MapperFactory $mapperFactory,
    ) {
        $this->client = $clientFactory->create();
    }

    /**
     * Returns all found positions of the address or the error, why the
     * address could not be geocoded.
     */
    public function getGeoCodeResult(string $address): GeoCodeResult
    {
        // Prevent calls to Map Providers GeoCode API, if address is empty
        if (trim($address) === '') {
            return new GeoCodeResult();
        }

        $geocodeRequest = $this->requestFactory->create('GeocodeRequest');
        $geocodeRequest->addParameter('address', $address);

        $clientResult = $this->getClientResult($geocodeRequest);
        if ($clientResult->response === []) {
            return new GeoCodeResult(error: $clientResult->error);
        }

        return new GeoCodeResult($this->mapperFactory->create()->map($clientResult->response));
    }

    /**
     * @return ObjectStorage|Position[]
     * @throws \Exception
     */
    public function getPositionsByAddress(string $address): ObjectStorage
    {
        return $this->getGeoCodeResult($address)->positions;
    }

    public function getFirstFoundPositionByAddress(string $address): ?Position
    {
        return $this->getGeoCodeResult($address)->getFirstPosition();
    }

    public function hasErrors(): bool
    {
        return $this->client->hasErrors();
    }

    /**
     * @return FlashMessage[]
     */
    public function getErrors(): array
    {
        return $this->client->getErrors();
    }

    /**
     * Custom clients implementing ClientInterface only can not return an error
     */
    protected function getClientResult(RequestInterface $geocodeRequest): ClientResult
    {
        if ($this->client instanceof AbstractClient) {
            return $this->client->getResult($geocodeRequest);
        }

        return new ClientResult($this->client->processRequest($geocodeRequest));
    }
}

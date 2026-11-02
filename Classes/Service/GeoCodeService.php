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
use JWeiland\Maps2\Client\ClientInterface;
use JWeiland\Maps2\Client\ClientResult;
use JWeiland\Maps2\Client\Request\RequestInterface;
use JWeiland\Maps2\Domain\Model\GeoCodeResult;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Mapper\MapperInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * With this class you can start requests to GeoCode API of Map Providers. Search for addresses, assign a POI
 * to a foreign record, save the foreign record, and many more. It is designed as an API.
 */
readonly class GeoCodeService
{
    public function __construct(
        protected ClientInterface $mapProviderClient,
        protected RequestInterface $mapProviderGeoCodingRequest,
        protected MapperInterface $mapper,
    ) {}

    /**
     * Returns all found positions of the address or the error, why the
     * address could not be geocoded.
     */
    public function getGeoCodeResult(string $address): GeoCodeResult
    {
        // Prevent calls to Map Providers GeoCode API if address is empty
        if (trim($address) === '') {
            return new GeoCodeResult();
        }

        $clientResult = $this->getClientResult($address);
        if ($clientResult->response === []) {
            return new GeoCodeResult(error: $clientResult->error);
        }

        return new GeoCodeResult($this->mapper->map($clientResult->response));
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
        return $this->mapProviderClient->hasErrors();
    }

    /**
     * @return FlashMessage[]
     */
    public function getErrors(): array
    {
        return $this->mapProviderClient->getErrors();
    }

    /**
     * Custom clients implementing ClientInterface only can not return an error
     */
    protected function getClientResult(string $address): ClientResult
    {
        if ($this->mapProviderClient instanceof AbstractClient) {
            return $this->mapProviderClient->getResult($this->mapProviderGeoCodingRequest, $address);
        }

        return new ClientResult(
            $this->mapProviderClient->processRequest($this->mapProviderGeoCodingRequest, $address),
        );
    }
}

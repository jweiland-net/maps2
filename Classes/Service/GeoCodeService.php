<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Service;

use JWeiland\Maps2\Client\ClientInterface;
use JWeiland\Maps2\Client\Request\RequestFactory;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Mapper\MapperFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * With this class you can start requests to GeoCode API of Map Providers. Search for addresses, assign a POI
 * to a foreign record, save the foreign record, and many more. It is designed as an API.
 */
class GeoCodeService
{
    public function __construct(
        protected ClientInterface $mapProviderClient,
        protected RequestFactory $requestFactory,
        protected MapperFactory $mapperFactory,
    ) {}

    /**
     * @return ObjectStorage|Position[]
     * @throws \Exception
     */
    public function getPositionsByAddress(string $address): ObjectStorage
    {
        $positions = new ObjectStorage();

        // Prevent calls to Map Providers GeoCode API if address is empty
        if (trim($address) === '') {
            return $positions;
        }

        $response = $this->mapProviderClient->processRequest(
            $this->requestFactory->create('GeocodeRequest'),
            $address,
        );

        if ($response !== []) {
            return $this->mapperFactory->create()->map($response);
        }

        return $positions;
    }

    public function getFirstFoundPositionByAddress(string $address): ?Position
    {
        $position = null;
        $positions = $this->getPositionsByAddress($address);
        if ($positions->count() !== 0) {
            $positions->rewind();
            /** @var Position $position */
            $position = $positions->current();
        }

        return $position;
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
}

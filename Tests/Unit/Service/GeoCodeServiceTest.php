<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Service;

use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Mapper\GoogleMapsMapper;
use JWeiland\Maps2\Mapper\MapperFactory;
use JWeiland\Maps2\Service\GeoCodeService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test GeoCode Service class
 */
class GeoCodeServiceTest extends UnitTestCase
{
    public $clientFactoryMock;
    public $requestFactoryMock;
    protected MockObject $mapProviderClient;

    protected MockObject $mapperFactoryMock;

    protected MockObject $gmGeocodeRequestMock;

    protected GeoCodeService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapProviderClient = $this->createMock(GoogleMapsClient::class);
        $this->mapperFactoryMock = $this->createMock(MapperFactory::class);
        $this->gmGeocodeRequestMock = $this->createMock(GeocodeRequest::class);

        $this->subject = new GeoCodeService(
            $this->mapProviderClient,
            $this->gmGeocodeRequestMock,
            $this->mapperFactoryMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->clientFactoryMock,
            $this->mapProviderClient,
            $this->requestFactoryMock,
            $this->mapperFactoryMock,
            $this->gmGeocodeRequestMock,
        );

        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    #[Test]
    public function getPositionsByAddressWithEmptyAddressWillReturnEmptyObjectStorage(): void
    {
        $objectStorage = new ObjectStorage();

        $positions = $this->subject->getPositionsByAddress('');

        self::assertSame(
            $objectStorage->toArray(),
            $positions->toArray(),
        );
    }

    #[Test]
    public function getPositionsByAddressWithAddressFilledWithSpacesWillReturnEmptyObjectStorage(): void
    {
        $objectStorage = new ObjectStorage();

        $positions = $this->subject->getPositionsByAddress('    ');

        self::assertSame(
            $objectStorage->toArray(),
            $positions->toArray(),
        );
    }

    #[Test]
    public function getPositionsByAddressWithEmptyResponseWillReturnEmptyObjectStorage(): void
    {
        $address = 'test street 123, 12345 city';
        $objectStorage = new ObjectStorage();

        $this->mapProviderClient
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn([]);

        $positions = $this->subject->getPositionsByAddress($address);

        self::assertSame(
            $objectStorage->toArray(),
            $positions->toArray(),
        );
    }

    #[Test]
    public function getPositionsByAddressWillReturnFilledObjectStorage(): void
    {
        $positions = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $positions);

        $response = [
            'results' => [
                0 => [
                    'formatted_address' => 'My street 123, 12345 somewhere',
                ],
            ],
        ];

        $this->mapProviderClient
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn($response);

        $googleMapsMapper = new GoogleMapsMapper();

        $this->mapperFactoryMock
            ->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($googleMapsMapper);

        self::assertCount(
            1,
            $this->subject->getPositionsByAddress('My private address'),
        );
    }

    #[Test]
    public function getFirstFoundPositionByAddressWithEmptyAddressWillReturnNull(): void
    {
        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        self::assertNull(
            $this->subject->getFirstFoundPositionByAddress(''),
        );
    }

    #[Test]
    public function getFirstFoundPositionByAddressWithAddressFilledWithSpacesWillReturnNull(): void
    {
        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        self::assertNull(
            $this->subject->getFirstFoundPositionByAddress('     '),
        );
    }

    #[Test]
    public function getFirstFoundPositionByAddressWithAddressWillReturnNull(): void
    {
        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        $this->mapProviderClient
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn([]);

        self::assertNull(
            $this->subject->getFirstFoundPositionByAddress('My private address'),
        );
    }

    #[Test]
    public function getFirstFoundPositionByAddressWillReturnRadiusResult(): void
    {
        $expectedPosition = new Position();
        $expectedPosition->setFormattedAddress('My street 123, 12345 somewhere');

        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        $response = [
            'results' => [
                0 => [
                    'formatted_address' => 'My street 123, 12345 somewhere',
                ],
            ],
        ];

        $this->mapProviderClient
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn($response);

        $googleMapsMapper = new GoogleMapsMapper();

        $this->mapperFactoryMock
            ->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($googleMapsMapper);

        self::assertEquals(
            $expectedPosition,
            $this->subject->getFirstFoundPositionByAddress('My private address'),
        );
    }
}

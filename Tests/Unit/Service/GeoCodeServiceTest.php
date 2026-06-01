<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Service;

use JWeiland\Maps2\Client;
use JWeiland\Maps2\Client\ClientFactory;
use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\Request;
use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest;
use JWeiland\Maps2\Client\Request\RequestFactory;
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
    /**
     * @var Client\ClientFactory|MockObject
     */
    protected MockObject $clientFactoryMock;

    /**
     * @var Client\GoogleMapsClient|MockObject
     */
    protected MockObject $googleMapsClientMock;

    /**
     * @var Request\RequestFactory|MockObject
     */
    protected MockObject $requestFactoryMock;

    /**
     * @var MapperFactory|MockObject
     */
    protected MockObject $mapperFactoryMock;

    /**
     * @var GeocodeRequest|MockObject
     */
    protected MockObject $gmGeocodeRequestMock;

    protected GeoCodeService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientFactoryMock = $this->createMock(ClientFactory::class);
        $this->googleMapsClientMock = $this->createMock(GoogleMapsClient::class);
        $this->requestFactoryMock = $this->createMock(RequestFactory::class);
        $this->mapperFactoryMock = $this->createMock(MapperFactory::class);
        $this->gmGeocodeRequestMock = $this->createMock(GeocodeRequest::class);

        $this->clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->googleMapsClientMock);

        $this->subject = new GeoCodeService(
            $this->clientFactoryMock,
            $this->requestFactoryMock,
            $this->mapperFactoryMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->clientFactoryMock,
            $this->googleMapsClientMock,
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

        self::assertInstanceOf(
            ObjectStorage::class,
            $positions,
        );

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

        self::assertInstanceOf(
            ObjectStorage::class,
            $positions,
        );

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

        $this->gmGeocodeRequestMock
            ->expects($this->atLeastOnce())
            ->method('addParameter')
            ->with(
                'address',
                $address,
            );

        $this->requestFactoryMock
            ->expects($this->atLeastOnce())
            ->method('create')
            ->with('GeocodeRequest')
            ->willReturn($this->gmGeocodeRequestMock);

        $this->googleMapsClientMock
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn([]);

        $positions = $this->subject->getPositionsByAddress($address);

        self::assertInstanceOf(
            ObjectStorage::class,
            $positions,
        );

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

        $this->gmGeocodeRequestMock
            ->expects($this->atLeastOnce())
            ->method('addParameter')
            ->with(
                'address',
                'My private address',
            );

        $this->requestFactoryMock
            ->expects($this->atLeastOnce())
            ->method('create')
            ->with('GeocodeRequest')
            ->willReturn($this->gmGeocodeRequestMock);

        $this->googleMapsClientMock
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

        $this->gmGeocodeRequestMock
            ->expects($this->atLeastOnce())
            ->method('addParameter')
            ->with(
                'address',
                'My private address',
            );

        $this->requestFactoryMock
            ->expects($this->atLeastOnce())
            ->method('create')
            ->with('GeocodeRequest')
            ->willReturn($this->gmGeocodeRequestMock);

        $this->googleMapsClientMock
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

        $this->gmGeocodeRequestMock
            ->expects($this->atLeastOnce())
            ->method('addParameter')
            ->with(
                'address',
                'My private address',
            );

        $this->requestFactoryMock
            ->expects($this->atLeastOnce())
            ->method('create')
            ->with('GeocodeRequest')
            ->willReturn($this->gmGeocodeRequestMock);

        $this->googleMapsClientMock
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

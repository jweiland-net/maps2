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
use JWeiland\Maps2\Client\Request;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Mapper\GoogleMapsMapper;
use JWeiland\Maps2\Mapper\MapperFactory;
use JWeiland\Maps2\Service\GeoCodeService;
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
    protected $clientFactoryMock;

    /**
     * @var Client\GoogleMapsClient|MockObject
     */
    protected $googleMapsClientMock;

    /**
     * @var Request\RequestFactory|MockObject
     */
    protected $requestFactoryMock;

    /**
     * @var MapperFactory|MockObject
     */
    protected $mapperFactoryMock;

    /**
     * @var Request\GoogleMaps\GeocodeRequest|MockObject
     */
    protected $gmGeocodeRequestMock;

    protected GeoCodeService $subject;

    protected function setUp(): void
    {
        $this->clientFactoryMock = $this->createMock(Client\ClientFactory::class);
        $this->googleMapsClientMock = $this->createMock(Client\GoogleMapsClient::class);
        $this->requestFactoryMock = $this->createMock(Request\RequestFactory::class);
        $this->mapperFactoryMock = $this->createMock(MapperFactory::class);
        $this->gmGeocodeRequestMock = $this->createMock(Request\GoogleMaps\GeocodeRequest::class);

        $this->clientFactoryMock
            ->expects(self::atLeastOnce())
            ->method('create')
            ->willReturn($this->googleMapsClientMock);

        $this->subject = new GeoCodeService(
            $this->clientFactoryMock,
            $this->requestFactoryMock,
            $this->mapperFactoryMock
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
            $this->gmGeocodeRequestMock
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getPositionsByAddressWithEmptyAddressWillReturnEmptyObjectStorage(): void
    {
        $objectStorage = new ObjectStorage();

        $positions = $this->subject->getPositionsByAddress('');

        self::assertInstanceOf(
            ObjectStorage::class,
            $positions
        );

        self::assertSame(
            $objectStorage->toArray(),
            $positions->toArray()
        );
    }

    /**
     * @test
     */
    public function getPositionsByAddressWithAddressFilledWithSpacesWillReturnEmptyObjectStorage(): void
    {
        $objectStorage = new ObjectStorage();

        $positions = $this->subject->getPositionsByAddress('    ');

        self::assertInstanceOf(
            ObjectStorage::class,
            $positions
        );

        self::assertSame(
            $objectStorage->toArray(),
            $positions->toArray()
        );
    }

    /**
     * @test
     *
     * @datProvider dataProviderForGeocodeRequests
     */
    public function getPositionsByAddressWithEmptyResponseWillReturnEmptyObjectStorage(): void
    {
        $address = 'test street 123, 12345 city';
        $objectStorage = new ObjectStorage();

        $this->gmGeocodeRequestMock
            ->expects(self::atLeastOnce())
            ->method('addParameter')
            ->with(
                'address',
                $address
            );

        $this->requestFactoryMock
            ->expects(self::atLeastOnce())
            ->method('create')
            ->with('GeocodeRequest')
            ->willReturn($this->gmGeocodeRequestMock);

        $this->googleMapsClientMock
            ->expects(self::atLeastOnce())
            ->method('processRequest')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn([]);

        $positions = $this->subject->getPositionsByAddress($address);

        self::assertInstanceOf(
            ObjectStorage::class,
            $positions
        );

        self::assertSame(
            $objectStorage->toArray(),
            $positions->toArray()
        );
    }

    /**
     * @test
     * @throws \Exception
     */
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
            ->expects(self::atLeastOnce())
            ->method('addParameter')
            ->with(
                'address',
                'My private address'
            );

        $this->requestFactoryMock
            ->expects(self::atLeastOnce())
            ->method('create')
            ->with('GeocodeRequest')
            ->willReturn($this->gmGeocodeRequestMock);

        $this->googleMapsClientMock
            ->expects(self::atLeastOnce())
            ->method('processRequest')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn($response);

        $googleMapsMapper = new GoogleMapsMapper();

        $this->mapperFactoryMock
            ->expects(self::atLeastOnce())
            ->method('create')
            ->willReturn($googleMapsMapper);

        self::assertCount(
            1,
            $this->subject->getPositionsByAddress('My private address')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFirstFoundPositionByAddressWithEmptyAddressWillReturnNull(): void
    {
        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        self::assertNull(
            $this->subject->getFirstFoundPositionByAddress('')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFirstFoundPositionByAddressWithAddressFilledWithSpacesWillReturnNull(): void
    {
        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        self::assertNull(
            $this->subject->getFirstFoundPositionByAddress('     ')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFirstFoundPositionByAddressWithAddressWillReturnNull(): void
    {
        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        $this->gmGeocodeRequestMock
            ->expects(self::atLeastOnce())
            ->method('addParameter')
            ->with(
                'address',
                'My private address'
            );

        $this->requestFactoryMock
            ->expects(self::atLeastOnce())
            ->method('create')
            ->with('GeocodeRequest')
            ->willReturn($this->gmGeocodeRequestMock);

        $this->googleMapsClientMock
            ->expects(self::atLeastOnce())
            ->method('processRequest')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn([]);

        self::assertNull(
            $this->subject->getFirstFoundPositionByAddress('My private address')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
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
            ->expects(self::atLeastOnce())
            ->method('addParameter')
            ->with(
                'address',
                'My private address'
            );

        $this->requestFactoryMock
            ->expects(self::atLeastOnce())
            ->method('create')
            ->with('GeocodeRequest')
            ->willReturn($this->gmGeocodeRequestMock);

        $this->googleMapsClientMock
            ->expects(self::atLeastOnce())
            ->method('processRequest')
            ->with($this->gmGeocodeRequestMock)
            ->willReturn($response);

        $googleMapsMapper = new GoogleMapsMapper();

        $this->mapperFactoryMock
            ->expects(self::atLeastOnce())
            ->method('create')
            ->willReturn($googleMapsMapper);

        self::assertEquals(
            $expectedPosition,
            $this->subject->getFirstFoundPositionByAddress('My private address')
        );
    }
}

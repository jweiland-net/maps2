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
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Test GeoCode Service class
 */
class GeoCodeServiceTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var Client\ClientFactory|ObjectProphecy
     */
    protected $clientFactoryProphecy;

    /**
     * @var Client\GoogleMapsClient|ObjectProphecy
     */
    protected $googleMapsClientProphecy;

    /**
     * @var Request\RequestFactory|ObjectProphecy
     */
    protected $requestFactoryProphecy;

    /**
     * @var MapperFactory|ObjectProphecy
     */
    protected $mapperFactoryProphecy;

    /**
     * @var Request\GoogleMaps\GeocodeRequest|ObjectProphecy
     */
    protected $gmGeocodeRequestProphecy;

    protected GeoCodeService $subject;

    protected function setUp(): void
    {
        $this->clientFactoryProphecy = $this->prophesize(Client\ClientFactory::class);
        $this->googleMapsClientProphecy = $this->prophesize(Client\GoogleMapsClient::class);
        $this->requestFactoryProphecy = $this->prophesize(Request\RequestFactory::class);
        $this->mapperFactoryProphecy = $this->prophesize(MapperFactory::class);
        $this->gmGeocodeRequestProphecy = $this->prophesize(Request\GoogleMaps\GeocodeRequest::class);

        $this->clientFactoryProphecy
            ->create()
            ->shouldBeCalled()
            ->willReturn($this->googleMapsClientProphecy->reveal());

        $this->subject = new GeoCodeService(
            $this->clientFactoryProphecy->reveal(),
            $this->requestFactoryProphecy->reveal(),
            $this->mapperFactoryProphecy->reveal()
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->clientFactoryProphecy,
            $this->googleMapsClientProphecy,
            $this->requestFactoryProphecy,
            $this->mapperFactoryProphecy,
            $this->gmGeocodeRequestProphecy
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

        $this->gmGeocodeRequestProphecy
            ->addParameter('address', $address)
            ->shouldBeCalled();

        $this->requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($this->gmGeocodeRequestProphecy);

        $this->googleMapsClientProphecy
            ->processRequest($this->gmGeocodeRequestProphecy->reveal())
            ->shouldBeCalled()
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

        $this->gmGeocodeRequestProphecy
            ->addParameter('address', 'My private address')
            ->shouldBeCalled();

        $this->requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($this->gmGeocodeRequestProphecy->reveal());

        $this->googleMapsClientProphecy
            ->processRequest($this->gmGeocodeRequestProphecy->reveal())
            ->shouldBeCalled()
            ->willReturn($response);

        $googleMapsMapper = new GoogleMapsMapper();

        $this->mapperFactoryProphecy
            ->create()
            ->shouldBeCalled()
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

        $this->gmGeocodeRequestProphecy
            ->addParameter('address', 'My private address')
            ->shouldBeCalled();

        $this->requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($this->gmGeocodeRequestProphecy->reveal());

        $this->googleMapsClientProphecy
            ->processRequest($this->gmGeocodeRequestProphecy->reveal())
            ->shouldBeCalled()
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

        $this->gmGeocodeRequestProphecy
            ->addParameter('address', 'My private address')
            ->shouldBeCalled();

        $this->requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($this->gmGeocodeRequestProphecy->reveal());

        $this->googleMapsClientProphecy
            ->processRequest($this->gmGeocodeRequestProphecy->reveal())
            ->shouldBeCalled()
            ->willReturn($response);

        $googleMapsMapper = new GoogleMapsMapper();

        $this->mapperFactoryProphecy
            ->create()
            ->shouldBeCalled()
            ->willReturn($googleMapsMapper);

        self::assertEquals(
            $expectedPosition,
            $this->subject->getFirstFoundPositionByAddress('My private address')
        );
    }
}

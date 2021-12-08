<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Service;

use JWeiland\Maps2\Client\ClientInterface;
use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\Request\GoogleMaps\GeocodeRequest;
use JWeiland\Maps2\Client\Request\RequestFactory;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Mapper\GoogleMapsMapper;
use JWeiland\Maps2\Mapper\MapperFactory;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Tests\Unit\AbstractUnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Test GeoCode Service class
 */
class GeoCodeServiceTest extends AbstractUnitTestCase
{
    /**
     * @var ClientInterface|ObjectProphecy
     */
    protected $clientProphecy;

    /**
     * @var RequestFactory|ObjectProphecy
     */
    protected $requestFactoryProphecy;

    /**
     * @var MapperFactory|ObjectProphecy
     */
    protected $mapperFactoryProphecy;

    /**
     * @var GeoCodeService
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->clientProphecy = $this->prophesize(GoogleMapsClient::class);
        $this->requestFactoryProphecy = $this->prophesize(RequestFactory::class);
        $this->mapperFactoryProphecy = $this->prophesize(MapperFactory::class);

        $this->subject = new GeoCodeService(
            $this->clientProphecy->reveal(),
            $this->requestFactoryProphecy->reveal(),
            $this->mapperFactoryProphecy->reveal()
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->client
        );
        parent::tearDown();
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getPositionsByAddressWithEmptyAddressWillReturnEmptyObjectStorage()
    {
        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getPositionsByAddress('')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getPositionsByAddressWithAddressFilledWithSpacesWillReturnEmptyObjectStorage()
    {
        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getPositionsByAddress('    ')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getPositionsByAddressWillReturnEmptyObjectStorage()
    {
        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        /** @var GeocodeRequest|ObjectProphecy $geocodeRequestProphecy */
        $geocodeRequestProphecy = $this->prophesize(GeocodeRequest::class);
        $geocodeRequestProphecy
            ->addParameter('address', 'My private address')
            ->shouldBeCalled();

        $this->requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($geocodeRequestProphecy->reveal());

        $this->clientProphecy
            ->processRequest($geocodeRequestProphecy->reveal())
            ->shouldBeCalled()
            ->willReturn([]);

        self::assertSame(
            $objectStorage,
            $this->subject->getPositionsByAddress('My private address')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getPositionsByAddressWillReturnFilledObjectStorage()
    {
        $positions = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $positions);

        $response = [
            'results' => [
                0 => [
                    'formatted_address' => 'My street 123, 12345 somewhere'
                ]
            ]
        ];

        /** @var GeocodeRequest|ObjectProphecy $geocodeRequestProphecy */
        $geocodeRequestProphecy = $this->prophesize(GeocodeRequest::class);
        $geocodeRequestProphecy
            ->addParameter('address', 'My private address')
            ->shouldBeCalled();

        $this->requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($geocodeRequestProphecy->reveal());

        $this->clientProphecy
            ->processRequest($geocodeRequestProphecy->reveal())
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
    public function getFirstFoundPositionByAddressWithEmptyAddressWillReturnNull()
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
    public function getFirstFoundPositionByAddressWithAddressFilledWithSpacesWillReturnNull()
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
    public function getFirstFoundPositionByAddressWithAddressWillReturnNull()
    {
        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        /** @var GeocodeRequest|ObjectProphecy $geocodeRequestProphecy */
        $geocodeRequestProphecy = $this->prophesize(GeocodeRequest::class);
        $geocodeRequestProphecy
            ->addParameter('address', 'My private address')
            ->shouldBeCalled();

        $this->requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($geocodeRequestProphecy->reveal());

        $this->clientProphecy
            ->processRequest($geocodeRequestProphecy->reveal())
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
    public function getFirstFoundPositionByAddressWillReturnRadiusResult()
    {
        $expectedPosition = new Position();
        $expectedPosition->setFormattedAddress('My street 123, 12345 somewhere');

        $objectStorage = new ObjectStorage();
        GeneralUtility::addInstance(ObjectStorage::class, $objectStorage);

        $response = [
            'results' => [
                0 => [
                    'formatted_address' => 'My street 123, 12345 somewhere'
                ]
            ]
        ];

        /** @var GeocodeRequest|ObjectProphecy $geocodeRequestProphecy */
        $geocodeRequestProphecy = $this->prophesize(GeocodeRequest::class);
        $geocodeRequestProphecy
            ->addParameter('address', 'My private address')
            ->shouldBeCalled();

        $this->requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($geocodeRequestProphecy->reveal());

        $this->clientProphecy
            ->processRequest($geocodeRequestProphecy->reveal())
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

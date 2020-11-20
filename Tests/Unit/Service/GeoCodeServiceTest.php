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
     * @var GeoCodeService
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->clientProphecy = $this->prophesize(GoogleMapsClient::class);

        $this->subject = new GeoCodeService($this->clientProphecy->reveal());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
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

        /** @var RequestFactory|ObjectProphecy $requestFactoryProphecy */
        $requestFactoryProphecy = $this->prophesize(RequestFactory::class);
        $requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($geocodeRequestProphecy->reveal());
        GeneralUtility::addInstance(RequestFactory::class, $requestFactoryProphecy->reveal());

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

        /** @var RequestFactory|ObjectProphecy $requestFactoryProphecy */
        $requestFactoryProphecy = $this->prophesize(RequestFactory::class);
        $requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($geocodeRequestProphecy->reveal());
        GeneralUtility::addInstance(RequestFactory::class, $requestFactoryProphecy->reveal());

        $this->clientProphecy
            ->processRequest($geocodeRequestProphecy->reveal())
            ->shouldBeCalled()
            ->willReturn($response);

        $googleMapsMapper = new GoogleMapsMapper();

        /** @var MapperFactory|ObjectProphecy $mapperFactoryProphecy */
        $mapperFactoryProphecy = $this->prophesize(MapperFactory::class);
        $mapperFactoryProphecy
            ->create()
            ->shouldBeCalled()
            ->willReturn($googleMapsMapper);
        GeneralUtility::addInstance(MapperFactory::class, $mapperFactoryProphecy->reveal());

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

        /** @var RequestFactory|ObjectProphecy $requestFactoryProphecy */
        $requestFactoryProphecy = $this->prophesize(RequestFactory::class);
        $requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($geocodeRequestProphecy->reveal());
        GeneralUtility::addInstance(RequestFactory::class, $requestFactoryProphecy->reveal());

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

        /** @var RequestFactory|ObjectProphecy $requestFactoryProphecy */
        $requestFactoryProphecy = $this->prophesize(RequestFactory::class);
        $requestFactoryProphecy
            ->create('GeocodeRequest')
            ->shouldBeCalled()
            ->willReturn($geocodeRequestProphecy->reveal());
        GeneralUtility::addInstance(RequestFactory::class, $requestFactoryProphecy->reveal());

        $this->clientProphecy
            ->processRequest($geocodeRequestProphecy->reveal())
            ->shouldBeCalled()
            ->willReturn($response);

        $googleMapsMapper = new GoogleMapsMapper();

        /** @var MapperFactory|ObjectProphecy $mapperFactoryProphecy */
        $mapperFactoryProphecy = $this->prophesize(MapperFactory::class);
        $mapperFactoryProphecy
            ->create()
            ->shouldBeCalled()
            ->willReturn($googleMapsMapper);
        GeneralUtility::addInstance(MapperFactory::class, $mapperFactoryProphecy->reveal());

        self::assertEquals(
            $expectedPosition,
            $this->subject->getFirstFoundPositionByAddress('My private address')
        );
    }
}

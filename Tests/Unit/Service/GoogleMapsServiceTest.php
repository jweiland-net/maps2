<?php
namespace JWeiland\Maps2\Tests\Unit\Service;

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

use Doctrine\DBAL\Driver\Statement;
use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\Request\GeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\Location;
use JWeiland\Maps2\Domain\Model\RadiusResult;
use JWeiland\Maps2\Service\GoogleMapsService;
use JWeiland\Maps2\Tests\Unit\AbstractUnitTestCase;
use JWeiland\Maps2\Utility\DataMapper;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Test Google Maps Service class
 */
class GoogleMapsServiceTest extends AbstractUnitTestCase
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var ObjectManager|ObjectProphecy
     */
    protected $objectManager;

    /**
     * @var StandaloneView|ObjectProphecy
     */
    protected $view;

    /**
     * @var UriBuilder|ObjectProphecy
     */
    protected $uriBuilder;

    /**
     * @var GoogleMapsClient|ObjectProphecy
     */
    protected $client;

    /**
     * @var GeocodeRequest|ObjectProphecy
     */
    protected $geocodeRequest;

    /**
     * @var DataMapper|ObjectProphecy
     */
    protected $dataMapper;

    /**
     * @var FlashMessageService|ObjectProphecy
     */
    protected $flashMessageService;

    /**
     * @var GoogleMapsService
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->extConf = new ExtConf();
        $this->objectManager = $this->prophesize(ObjectManager::class);
        $this->view = $this->prophesize(StandaloneView::class);
        $this->uriBuilder = $this->prophesize(UriBuilder::class);
        $this->client = $this->prophesize(GoogleMapsClient::class);
        $this->geocodeRequest = $this->prophesize(GeocodeRequest::class);
        $this->dataMapper = $this->prophesize(DataMapper::class);
        $this->flashMessageService = $this->prophesize(FlashMessageService::class);

        $this->subject = new GoogleMapsService();
        $this->subject->injectExtConf($this->extConf);
        $this->subject->injectObjectManager($this->objectManager->reveal());
        $this->subject->injectFlashMessageService($this->flashMessageService->reveal());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function showAllowMapFormAssignsSettingsAndRequestUriToView()
    {
        $arguments = [
            'tx_maps2_maps2' => [
                'googleRequestsAllowedForMaps2' => 1
            ]
        ];

        $this->view
            ->setTemplatePathAndFilename(Argument::any())
            ->shouldBeCalled();
        $this->view
            ->assign('settings', [])
            ->shouldBeCalled();
        $this->view
            ->assign('requestUri', 'MyCoolRequestUri')
            ->shouldBeCalled();
        $this->view
            ->render()
            ->shouldBeCalled();

        $this->uriBuilder
            ->reset()
            ->shouldBeCalled()
            ->willReturn($this->uriBuilder->reveal());
        $this->uriBuilder
            ->setAddQueryString(true)
            ->shouldBeCalled()
            ->willReturn($this->uriBuilder->reveal());
        $this->uriBuilder
            ->setArguments($arguments)
            ->shouldBeCalled()
            ->willReturn($this->uriBuilder->reveal());
        $this->uriBuilder
            ->setArgumentsToBeExcludedFromQueryString(['cHash'])
            ->shouldBeCalled()
            ->willReturn($this->uriBuilder->reveal());
        $this->uriBuilder
            ->build()
            ->shouldBeCalled()
            ->willReturn('MyCoolRequestUri');

        $this->objectManager
            ->get(StandaloneView::class)
            ->shouldBeCalled()
            ->willReturn($this->view->reveal());
        $this->objectManager
            ->get(UriBuilder::class)
            ->shouldBeCalled()
            ->willReturn($this->uriBuilder->reveal());

        $this->subject->showAllowMapForm();
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getPositionsByAddressWillReturnEmptyObjectStorage()
    {
        $emptyObjectStorage = new ObjectStorage();

        $this->geocodeRequest
            ->setAddress('My private address')
            ->shouldBeCalled();

        $this->client
            ->processRequest($this->geocodeRequest->reveal())
            ->shouldBeCalled()
            ->willReturn([]);

        $this->objectManager
            ->get(ObjectStorage::class)
            ->shouldBeCalled()
            ->willReturn($emptyObjectStorage);
        $this->objectManager
            ->get(GoogleMapsClient::class)
            ->shouldBeCalled()
            ->willReturn($this->client->reveal());
        $this->objectManager
            ->get(GeocodeRequest::class)
            ->shouldBeCalled()
            ->willReturn($this->geocodeRequest->reveal());

        $this->assertSame(
            $emptyObjectStorage,
            $this->subject->getPositionsByAddress('My private address')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getPositionsByAddressWillReturnFilledObjectStorage()
    {
        $position = new RadiusResult();
        $position->setFormattedAddress('My street 123, 12345 somewhere');

        $positions = new ObjectStorage();
        $positions->attach($position);

        $response = [
            'results' => [
                0 => [
                    'formatted_address' => 'My street 123, 12345 somewhere'
                ]
            ]
        ];

        $this->geocodeRequest
            ->setAddress('My private address')
            ->shouldBeCalled();

        $this->client
            ->processRequest($this->geocodeRequest->reveal())
            ->shouldBeCalled()
            ->willReturn($response);

        $this->dataMapper
            ->mapObjectStorage(RadiusResult::class, $response['results'])
            ->shouldBeCalled()
            ->willReturn($positions);

        $this->objectManager
            ->get(ObjectStorage::class)
            ->shouldBeCalled()
            ->willReturn(new ObjectStorage());
        $this->objectManager
            ->get(GoogleMapsClient::class)
            ->shouldBeCalled()
            ->willReturn($this->client->reveal());
        $this->objectManager
            ->get(GeocodeRequest::class)
            ->shouldBeCalled()
            ->willReturn($this->geocodeRequest->reveal());
        $this->objectManager
            ->get(DataMapper::class)
            ->shouldBeCalled()
            ->willReturn($this->dataMapper->reveal());

        $this->assertSame(
            $positions,
            $this->subject->getPositionsByAddress('My private address')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFirstFoundPositionByAddressWillReturnNull()
    {
        $emptyObjectStorage = new ObjectStorage();

        $this->geocodeRequest
            ->setAddress('My private address')
            ->shouldBeCalled();

        $this->client
            ->processRequest($this->geocodeRequest->reveal())
            ->shouldBeCalled()
            ->willReturn([]);

        $this->objectManager
            ->get(ObjectStorage::class)
            ->shouldBeCalled()
            ->willReturn($emptyObjectStorage);
        $this->objectManager
            ->get(GoogleMapsClient::class)
            ->shouldBeCalled()
            ->willReturn($this->client->reveal());
        $this->objectManager
            ->get(GeocodeRequest::class)
            ->shouldBeCalled()
            ->willReturn($this->geocodeRequest->reveal());

        $this->assertSame(
            null,
            $this->subject->getFirstFoundPositionByAddress('My private address')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFirstFoundPositionByAddressWillReturnRadiusResult()
    {
        $position = new RadiusResult();
        $position->setFormattedAddress('My street 123, 12345 somewhere');

        $positions = new ObjectStorage();
        $positions->attach($position);

        $response = [
            'results' => [
                0 => [
                    'formatted_address' => 'My street 123, 12345 somewhere'
                ]
            ]
        ];

        $this->geocodeRequest
            ->setAddress('My private address')
            ->shouldBeCalled();

        $this->client
            ->processRequest($this->geocodeRequest->reveal())
            ->shouldBeCalled()
            ->willReturn($response);

        $this->dataMapper
            ->mapObjectStorage(RadiusResult::class, $response['results'])
            ->shouldBeCalled()
            ->willReturn($positions);

        $this->objectManager
            ->get(ObjectStorage::class)
            ->shouldBeCalled()
            ->willReturn(new ObjectStorage());
        $this->objectManager
            ->get(GoogleMapsClient::class)
            ->shouldBeCalled()
            ->willReturn($this->client->reveal());
        $this->objectManager
            ->get(GeocodeRequest::class)
            ->shouldBeCalled()
            ->willReturn($this->geocodeRequest->reveal());
        $this->objectManager
            ->get(DataMapper::class)
            ->shouldBeCalled()
            ->willReturn($this->dataMapper->reveal());

        $this->assertSame(
            $position,
            $this->subject->getFirstFoundPositionByAddress('My private address')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function createNewPoiCollectionWithBrokenRadiusResultReturns0()
    {
        /** @var FlashMessage|ObjectProphecy $flashMessage */
        $flashMessage = $this->prophesize(FlashMessage::class);
        GeneralUtility::addInstance(FlashMessage::class, $flashMessage->reveal());

        /** @var FlashMessageQueue|ObjectProphecy $flashMessageQueue */
        $flashMessageQueue = $this->prophesize(FlashMessageQueue::class);
        $this->flashMessageService
            ->getMessageQueueByIdentifier()
            ->shouldBeCalled()
            ->willReturn($flashMessageQueue->reveal());

        $this->assertSame(
            0,
            $this->subject->createNewPoiCollection(
                123,
                new RadiusResult()
            )
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function createNewPoiCollectionWithRadiusResultReturnsUid()
    {
        $fieldValues = [
            'pid' => 123,
            'hidden' => 0,
            'deleted' => 0,
            'title' => 'My private address',
        ];

        $location = new Location();
        $location->setLat(123);
        $location->setLng(321);
        $geometry = new RadiusResult\Geometry();
        $geometry->setLocation($location);
        $radiusResult = new RadiusResult();
        $radiusResult->setGeometry($geometry);
        $radiusResult->setFormattedAddress('My private address');

        /** @var Statement|ObjectProphecy $statement */
        $statement = $this->prophesize(Statement::class);
        $statement->fetch()->shouldBeCalled()->willReturn(
            ['Field' => 'uid'],
            ['Field' => 'pid'],
            ['Field' => 'hidden'],
            ['Field' => 'deleted'],
            ['Field' => 'title'],
            null
        );

        /** @var Connection|ObjectProphecy $connection */
        $connection = $this->prophesize(Connection::class);
        $connection
            ->query('SHOW FULL COLUMNS FROM `tx_maps2_domain_model_poicollection`')
            ->shouldBeCalled()
            ->willReturn($statement->reveal());

        /** @var ConnectionPool|ObjectProphecy $selectConnectionPool */
        $selectConnectionPool = $this->prophesize(ConnectionPool::class);
        $selectConnectionPool
            ->getConnectionForTable('tx_maps2_domain_model_poicollection')
            ->shouldBeCalled()
            ->willReturn($connection->reveal());

        GeneralUtility::addInstance(ConnectionPool::class, $selectConnectionPool->reveal());

        /** @var Connection|ObjectProphecy $insertConnection */
        $insertConnection = $this->prophesize(Connection::class);
        $insertConnection
            ->insert(
                'tx_maps2_domain_model_poicollection',
                $fieldValues
            )
            ->shouldBeCalled();
        $insertConnection
            ->lastInsertId('tx_maps2_domain_model_poicollection')
            ->shouldBeCalled()
            ->willReturn(100);

        /** @var ConnectionPool|ObjectProphecy $insertConnectionPool */
        $insertConnectionPool = $this->prophesize(ConnectionPool::class);
        $insertConnectionPool
            ->getConnectionForTable('tx_maps2_domain_model_poicollection')
            ->shouldBeCalled()
            ->willReturn($insertConnection->reveal());

        GeneralUtility::addInstance(ConnectionPool::class, $insertConnectionPool->reveal());

        $this->assertSame(
            100,
            $this->subject->createNewPoiCollection(
                123,
                $radiusResult
            )
        );
    }
}

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
use JWeiland\Maps2\Helper\MessageHelper;
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
    protected $objectManagerProphecy;

    /**
     * @var StandaloneView|ObjectProphecy
     */
    protected $viewProphecy;

    /**
     * @var UriBuilder|ObjectProphecy
     */
    protected $uriBuilderProphecy;

    /**
     * @var GoogleMapsClient|ObjectProphecy
     */
    protected $clientProphecy;

    /**
     * @var GeocodeRequest|ObjectProphecy
     */
    protected $geocodeRequestProphecy;

    /**
     * @var DataMapper|ObjectProphecy
     */
    protected $dataMapperProphecy;

    /**
     * @var MessageHelper|ObjectProphecy
     */
    protected $messageHelperProphecy;

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
        $this->extConf->setAllowMapTemplatePath('typo3conf/ext/maps2/Resources/Private/Templates/AllowMapForm.html');
        $this->objectManagerProphecy = $this->prophesize(ObjectManager::class);
        $this->viewProphecy = $this->prophesize(StandaloneView::class);
        $this->uriBuilderProphecy = $this->prophesize(UriBuilder::class);
        $this->clientProphecy = $this->prophesize(GoogleMapsClient::class);
        $this->geocodeRequestProphecy = $this->prophesize(GeocodeRequest::class);
        $this->dataMapperProphecy = $this->prophesize(DataMapper::class);
        $this->messageHelperProphecy = $this->prophesize(MessageHelper::class);

        $this->subject = new GoogleMapsService();
        $this->subject->injectExtConf($this->extConf);
        $this->subject->injectObjectManager($this->objectManagerProphecy->reveal());
        $this->subject->injectMessageHelper($this->messageHelperProphecy->reveal());
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
                'mapProviderRequestsAllowedForMaps2' => 1
            ]
        ];

        $this->viewProphecy->setTemplatePathAndFilename(Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('settings', [])->shouldBeCalled();
        $this->viewProphecy->assign('requestUri', 'MyCoolRequestUri')->shouldBeCalled();
        $this->viewProphecy->render()->shouldBeCalled()->willReturn('');

        $this->uriBuilderProphecy
            ->reset()
            ->shouldBeCalled()
            ->willReturn($this->uriBuilderProphecy->reveal());
        $this->uriBuilderProphecy
            ->setAddQueryString(true)
            ->shouldBeCalled()
            ->willReturn($this->uriBuilderProphecy->reveal());
        $this->uriBuilderProphecy
            ->setArguments($arguments)
            ->shouldBeCalled()
            ->willReturn($this->uriBuilderProphecy->reveal());
        $this->uriBuilderProphecy
            ->setArgumentsToBeExcludedFromQueryString(['cHash'])
            ->shouldBeCalled()
            ->willReturn($this->uriBuilderProphecy->reveal());
        $this->uriBuilderProphecy
            ->build()
            ->shouldBeCalled()
            ->willReturn('MyCoolRequestUri');

        $this->objectManagerProphecy
            ->get(StandaloneView::class)
            ->shouldBeCalled()
            ->willReturn($this->viewProphecy->reveal());
        $this->objectManagerProphecy
            ->get(UriBuilder::class)
            ->shouldBeCalled()
            ->willReturn($this->uriBuilderProphecy->reveal());

        $this->subject->showAllowMapForm();
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getPositionsByAddressWillReturnEmptyObjectStorage()
    {
        $emptyObjectStorage = new ObjectStorage();

        $this->geocodeRequestProphecy
            ->setAddress('My private address')
            ->shouldBeCalled();

        $this->clientProphecy
            ->processRequest($this->geocodeRequestProphecy->reveal())
            ->shouldBeCalled()
            ->willReturn([]);

        $this->objectManagerProphecy
            ->get(ObjectStorage::class)
            ->shouldBeCalled()
            ->willReturn($emptyObjectStorage);
        $this->objectManagerProphecy
            ->get(GoogleMapsClient::class)
            ->shouldBeCalled()
            ->willReturn($this->clientProphecy->reveal());
        $this->objectManagerProphecy
            ->get(GeocodeRequest::class)
            ->shouldBeCalled()
            ->willReturn($this->geocodeRequestProphecy->reveal());

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

        $this->geocodeRequestProphecy
            ->setAddress('My private address')
            ->shouldBeCalled();

        $this->clientProphecy
            ->processRequest($this->geocodeRequestProphecy->reveal())
            ->shouldBeCalled()
            ->willReturn($response);

        $this->dataMapperProphecy
            ->mapObjectStorage(RadiusResult::class, $response['results'])
            ->shouldBeCalled()
            ->willReturn($positions);

        $this->objectManagerProphecy
            ->get(ObjectStorage::class)
            ->shouldBeCalled()
            ->willReturn(new ObjectStorage());
        $this->objectManagerProphecy
            ->get(GoogleMapsClient::class)
            ->shouldBeCalled()
            ->willReturn($this->clientProphecy->reveal());
        $this->objectManagerProphecy
            ->get(GeocodeRequest::class)
            ->shouldBeCalled()
            ->willReturn($this->geocodeRequestProphecy->reveal());
        $this->objectManagerProphecy
            ->get(DataMapper::class)
            ->shouldBeCalled()
            ->willReturn($this->dataMapperProphecy->reveal());

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

        $this->geocodeRequestProphecy
            ->setAddress('My private address')
            ->shouldBeCalled();

        $this->clientProphecy
            ->processRequest($this->geocodeRequestProphecy->reveal())
            ->shouldBeCalled()
            ->willReturn([]);

        $this->objectManagerProphecy
            ->get(ObjectStorage::class)
            ->shouldBeCalled()
            ->willReturn($emptyObjectStorage);
        $this->objectManagerProphecy
            ->get(GoogleMapsClient::class)
            ->shouldBeCalled()
            ->willReturn($this->clientProphecy->reveal());
        $this->objectManagerProphecy
            ->get(GeocodeRequest::class)
            ->shouldBeCalled()
            ->willReturn($this->geocodeRequestProphecy->reveal());

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

        $this->geocodeRequestProphecy
            ->setAddress('My private address')
            ->shouldBeCalled();

        $this->clientProphecy
            ->processRequest($this->geocodeRequestProphecy->reveal())
            ->shouldBeCalled()
            ->willReturn($response);

        $this->dataMapperProphecy
            ->mapObjectStorage(RadiusResult::class, $response['results'])
            ->shouldBeCalled()
            ->willReturn($positions);

        $this->objectManagerProphecy
            ->get(ObjectStorage::class)
            ->shouldBeCalled()
            ->willReturn(new ObjectStorage());
        $this->objectManagerProphecy
            ->get(GoogleMapsClient::class)
            ->shouldBeCalled()
            ->willReturn($this->clientProphecy->reveal());
        $this->objectManagerProphecy
            ->get(GeocodeRequest::class)
            ->shouldBeCalled()
            ->willReturn($this->geocodeRequestProphecy->reveal());
        $this->objectManagerProphecy
            ->get(DataMapper::class)
            ->shouldBeCalled()
            ->willReturn($this->dataMapperProphecy->reveal());

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
        /*$this->flashMessageService
            ->getMessageQueueByIdentifier()
            ->shouldBeCalled()
            ->willReturn($flashMessageQueue->reveal());*/

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

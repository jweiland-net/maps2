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
use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\Request\GeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\RadiusResult;
use JWeiland\Maps2\Service\GoogleMapsService;
use JWeiland\Maps2\Utility\DataMapper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Test Google Maps Service class
 */
class GoogleMapsServiceTest extends UnitTestCase
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

        $this->subject = new GoogleMapsService();
        $this->subject->injectExtConf($this->extConf);
        $this->subject->injectObjectManager($this->objectManager->reveal());
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
    public function findPositionsByAddressWillReturnEmptyObjectStorage()
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
            $this->subject->findPositionsByAddress('My private address')
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function findPositionsByAddressWillReturnFilledObjectStorage()
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
            $this->subject->findPositionsByAddress('My private address')
        );
    }
}

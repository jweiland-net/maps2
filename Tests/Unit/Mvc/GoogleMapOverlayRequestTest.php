<?php
namespace JWeiland\Maps2\Tests\Unit\Mvc;

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

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Mvc\MapProviderOverlayRequestHandler;
use JWeiland\Maps2\Service\GoogleMapsService;
use JWeiland\Maps2\Service\MapProviderRequestService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\RequestBuilder;
use TYPO3\CMS\Extbase\Service\EnvironmentService;

/**
 * Class GoogleMapOverlayRequestTest
 */
class GoogleMapOverlayRequestTest extends UnitTestCase
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var GoogleMapsService
     */
    protected $googleMapsService;

    /**
     * @var MapProviderRequestService
     */
    protected $mapProviderRequestService;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|EnvironmentService
     */
    protected $environmentService;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|RequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|Request
     */
    protected $request;

    /**
     * @var MapProviderOverlayRequestHandler
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $_SESSION['mapProviderRequestsAllowedForMaps2'] = false;

        $this->extConf = new ExtConf();
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(1);

        $this->googleMapsService = new GoogleMapsService();
        $this->mapProviderRequestService = new MapProviderRequestService($this->extConf);
        $this->environmentService = $this->prophesize(EnvironmentService::class);
        $this->requestBuilder = $this->prophesize(RequestBuilder::class);
        $this->request = $this->prophesize(Request::class);

        $this->subject = new MapProviderOverlayRequestHandler();
        $this->subject->injectEnvironmentService($this->environmentService->reveal());
        // $this->subject->injectGoogleRequestService($this->mapProviderRequestService);
        $this->subject->injectRequestBuilder($this->requestBuilder->reveal());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->googleMapsService, $this->mapProviderRequestService, $this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function canHandleRequestWillReturnFalseInCliContext()
    {
        $this->environmentService->isEnvironmentInCliMode()->shouldBeCalled()->willReturn(true);

        $this->assertFalse(
            $this->subject->canHandleRequest()
        );
    }

    /**
     * @test
     */
    public function canHandleRequestWillReturnFalseWhenExtKeyIsNotMaps2()
    {
        $this->environmentService->isEnvironmentInCliMode()->shouldBeCalled()->willReturn(false);
        $this->request->getControllerExtensionKey()->shouldBeCalled()->willReturn('events2');
        $this->requestBuilder->build()->shouldBeCalled()->willReturn($this->request->reveal());

        $this->assertFalse(
            $this->subject->canHandleRequest()
        );
    }

    /**
     * @test
     */
    public function canHandleRequestWillReturnTrueWhenExtKeyIsMaps2()
    {
        $this->environmentService->isEnvironmentInCliMode()->shouldBeCalled()->willReturn(false);
        $this->request->getControllerExtensionKey()->shouldBeCalled()->willReturn('maps2');
        $this->requestBuilder->build()->shouldBeCalled()->willReturn($this->request->reveal());

        $this->assertTrue(
            $this->subject->canHandleRequest()
        );
    }

    /**
     * @test
     */
    public function getPriorityReturnsAHigherValueThan100()
    {
        $this->assertGreaterThan(
            100,
            $this->subject->getPriority()
        );
    }
}

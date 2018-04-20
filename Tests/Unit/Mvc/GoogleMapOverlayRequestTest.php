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
use JWeiland\Maps2\Mvc\GoogleMapOverlayRequestHandler;
use JWeiland\Maps2\Service\GoogleRequestService;
use JWeiland\Maps2\Service\MapService;
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
     * @var MapService
     */
    protected $mapService;

    /**
     * @var GoogleRequestService
     */
    protected $googleRequestService;

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
     * @var GoogleMapOverlayRequestHandler
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->extConf = new ExtConf();
        $this->extConf->setExplicitAllowGoogleMaps(1);
        $this->extConf->setExplicitAllowGoogleMapsBySessionOnly(1);

        $this->mapService = new MapService();
        $this->googleRequestService = new GoogleRequestService($this->extConf);
        $this->environmentService = $this->prophesize(EnvironmentService::class);
        $this->requestBuilder = $this->prophesize(RequestBuilder::class);
        $this->request = $this->prophesize(Request::class);

        $this->subject = new GoogleMapOverlayRequestHandler();
        $this->subject->injectEnvironmentService($this->environmentService->reveal());
        $this->subject->injectGoogleRequestService($this->googleRequestService);
        $this->subject->injectRequestBuilder($this->requestBuilder->reveal());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->mapService, $this->googleRequestService, $this->subject);
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
        $this->requestBuilder->build()->shouldBeCalled()->willReturn($this->request);

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
        $this->requestBuilder->build()->shouldBeCalled()->willReturn($this->request);

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

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
use JWeiland\Maps2\Service\GoogleRequestService;
use JWeiland\Maps2\Service\MapService;
use JWeiland\Maps2\ViewHelpers\Widget\EditPoiViewHelper;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class EditPoiViewHelperTest
 */
class EditPoiViewHelperTest extends UnitTestCase
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|MapService
     */
    protected $mapService;

    /**
     * @var GoogleRequestService
     */
    protected $googleRequestService;

    /**
     * @var EditPoiViewHelper
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

        $this->mapService = $this->prophesize(MapService::class);
        $this->googleRequestService = new GoogleRequestService($this->extConf);

        $this->subject = new EditPoiViewHelper();
        $this->subject->injectGoogleRequestService($this->googleRequestService);
        $this->subject->injectMapService($this->mapService->reveal());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->extConf, $this->mapService, $this->googleRequestService, $this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderWillCallShowAllowMapFormWhenGoogleRequestsAreNotAllowed()
    {
        $this->mapService->showAllowMapForm()->shouldBeCalled()->willReturn('Please activate maps2');

        $this->assertSame(
            'Please activate maps2',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWillReturnAWidgetSubRequestObjectWhenGoogleRequestsAreAllowed()
    {
        $this->extConf->setExplicitAllowGoogleMaps(0);
        $this->extConf->setExplicitAllowGoogleMapsBySessionOnly(0);
        $this->mapService->showAllowMapForm()->shouldNotBeCalled();

        /** @var \Prophecy\Prophecy\ObjectProphecy|EditPoiViewHelper $object */
        $object = $this->prophesize(EditPoiViewHelper::class);
        $object->injectGoogleRequestService($this->googleRequestService)->shouldBeCalled();
        $object->injectMapService($this->mapService->reveal())->shouldBeCalled();
        $object->render()->shouldBeCalled()->willReturn('maps2 output');

        /** @var EditPoiViewHelper $subject */
        $subject = $object->reveal();
        $subject->injectGoogleRequestService($this->googleRequestService);
        $subject->injectMapService($this->mapService->reveal());

        $this->assertSame(
            'maps2 output',
            $subject->render()
        );
    }
}

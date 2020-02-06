<?php
namespace JWeiland\Maps2\Tests\Unit\ViewHelpers\Widget;

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
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapProviderRequestService;
use JWeiland\Maps2\Service\MapService;
use JWeiland\Maps2\ViewHelpers\Widget\PoiCollectionViewHelper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PoiCollectionViewHelper
 */
class PoiCollectionViewHelperTest extends UnitTestCase
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|GeoCodeService
     */
    protected $googleMapsService;

    /**
     * @var MapProviderRequestService
     */
    protected $mapProviderRequestService;

    /**
     * @var PoiCollectionViewHelper
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
        GeneralUtility::setSingletonInstance(ExtConf::class, $this->extConf);

        $mapProviderRequestService = new MapProviderRequestService();
        GeneralUtility::addInstance(MapProviderRequestService::class, $mapProviderRequestService);

        $this->subject = new PoiCollectionViewHelper();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->extConf, $this->googleMapsService, $this->mapProviderRequestService, $this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderWillCallShowAllowMapFormWhenGoogleRequestsAreNotAllowed()
    {
        /** @var MapService|ObjectProphecy $mapServiceProphecy */
        $mapServiceProphecy = $this->prophesize(MapService::class);
        $mapServiceProphecy->showAllowMapForm()->shouldBeCalled()->willReturn('Please activate maps2');
        GeneralUtility::addInstance(MapService::class, $mapServiceProphecy->reveal());

        $this->assertSame(
            'Please activate maps2',
            $this->subject->render()
        );
    }
}

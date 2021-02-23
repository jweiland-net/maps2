<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\ViewHelpers\Widget;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Service\MapProviderRequestService;
use JWeiland\Maps2\Service\MapService;
use JWeiland\Maps2\ViewHelpers\Widget\Controller\PoiCollectionController;
use JWeiland\Maps2\ViewHelpers\Widget\PoiCollectionViewHelper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class PoiCollectionViewHelper
 */
class PoiCollectionViewHelperTest extends UnitTestCase
{
    /**
     * @var PoiCollectionViewHelper
     */
    protected $subject;

    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var MapService|ObjectProphecy
     */
    protected $mapServiceProphecy;

    protected function setUp()
    {
        $_SESSION['mapProviderRequestsAllowedForMaps2'] = false;

        $this->extConf = new ExtConf([]);
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(1);
        GeneralUtility::setSingletonInstance(ExtConf::class, $this->extConf);

        $this->mapServiceProphecy = $this->prophesize(MapService::class);

        $this->subject = new PoiCollectionViewHelper();
        $this->subject->setRenderingContext($this->prophesize(RenderingContextInterface::class)->reveal());
        $this->subject->injectController($this->prophesize(PoiCollectionController::class)->reveal());
        $this->subject->injectMapProviderRequestService(new MapProviderRequestService());
        $this->subject->injectMapService($this->mapServiceProphecy->reveal());
    }

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
        $this->mapServiceProphecy->showAllowMapForm()->shouldBeCalled()->willReturn('Please activate maps2');

        self::assertSame(
            'Please activate maps2',
            $this->subject->render()
        );
    }
}

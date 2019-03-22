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

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Service\MapProviderRequestService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test MapProviderRequestService
 */
class MapProviderRequestServiceTest extends UnitTestCase
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var MapProviderRequestService
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->extConf = new ExtConf();
        GeneralUtility::setSingletonInstance(ExtConf::class, $this->extConf);

        $this->subject = new MapProviderRequestService();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->extConf, $this->subject, $GLOBALS['TSFE'], $_SESSION);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function isRequestToMapProviderAllowedWithDeactivatedSettingsWillReturnTrue()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(0);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);
        $this->assertTrue(
            $this->subject->isRequestToMapProviderAllowed()
        );
    }

    /**
     * @test
     */
    public function isRequestToMapProviderAllowedOnSessionUsageWillReturnFalse()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(1);
        $this->assertFalse(
            $this->subject->isRequestToMapProviderAllowed()
        );
    }

    /**
     * @test
     */
    public function isRequestToMapProviderAllowedOnSessionUsageWillReturnTrue()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(1);
        $_SESSION['mapProviderRequestsAllowedForMaps2'] = 1;
        $this->assertTrue(
            $this->subject->isRequestToMapProviderAllowed()
        );
    }

    /**
     * @test
     */
    public function isRequestToMapProviderAllowedOnCookieUsageWillReturnFalseIfTsfeIsNotSet()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);
        $this->assertFalse(
            $this->subject->isRequestToMapProviderAllowed()
        );
    }

    /**
     * @test
     */
    public function isRequestToMapProviderAllowedOnCookieUsageWillReturnFalse()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);

        $this->prophesize(TypoScriptFrontendController::class);
        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();

        /** @var FrontendUserAuthentication|ObjectProphecy $feUser */
        $feUser = $this->prophesize(FrontendUserAuthentication::class);
        $feUser->getSessionData('mapProviderRequestsAllowedForMaps2')->shouldBeCalled()->willReturn(false);
        $GLOBALS['TSFE']->fe_user = $feUser->reveal();

        $this->assertFalse(
            $this->subject->isRequestToMapProviderAllowed()
        );
    }

    /**
     * @test
     */
    public function isRequestToMapProviderAllowedOnCookieUsageWillReturnTrue()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);

        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();

        /** @var FrontendUserAuthentication|ObjectProphecy $feUser */
        $feUser = $this->prophesize(FrontendUserAuthentication::class);
        $feUser->getSessionData('mapProviderRequestsAllowedForMaps2')->shouldBeCalled()->willReturn(true);
        $GLOBALS['TSFE']->fe_user = $feUser->reveal();

        $this->assertTrue(
            $this->subject->isRequestToMapProviderAllowed()
        );
    }
}

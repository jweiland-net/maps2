<?php
namespace JWeiland\Maps2\Tests\Unit\Condition;

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

use JWeiland\Maps2\Condition\AllowMapProviderRequestCondition;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Service\MapProviderRequestService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class AllowGoogleRequestConditionTest
 */
class AllowGoogleRequestConditionTest extends UnitTestCase
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var MapProviderRequestService
     */
    protected $mapProviderRequestService;

    /**
     * @var AllowMapProviderRequestCondition
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->extConf = new ExtConf();
        $this->mapProviderRequestService = new MapProviderRequestService($this->extConf);
        $this->subject = new AllowMapProviderRequestCondition();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->extConf, $this->mapProviderRequestService, $this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function matchConditionWithDeactivatedSettingsWillReturnTrue()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(0);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);
        $this->assertTrue(
            $this->subject->matchCondition([])
        );
    }

    /**
     * @test
     */
    public function matchConditionOnSessionUsageWillReturnFalse()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(1);
        $this->assertFalse(
            $this->subject->matchCondition([])
        );
    }

    /**
     * @test
     */
    public function matchConditionOnSessionUsageWillReturnTrue()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(1);
        $_SESSION['mapProviderRequestsAllowedForMaps2'] = 1;
        $this->assertTrue(
            $this->subject->matchCondition([])
        );
    }

    /**
     * @test
     */
    public function matchConditionOnCookieUsageWillReturnFalseIfTsfeIsNotSet()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);
        $this->assertFalse(
            $this->subject->matchCondition([])
        );
    }

    /**
     * @test
     */
    public function matchConditionOnCookieUsageWillReturnFalse()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);
        $this->prophesize(TypoScriptFrontendController::class);
        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();
        /** @var \Prophecy\Prophecy\ObjectProphecy|FrontendUserAuthentication $feUser */
        $feUser = $this->prophesize(FrontendUserAuthentication::class);
        $feUser->getSessionData('mapProviderRequestsAllowedForMaps2')->shouldBeCalled()->willReturn(false);
        $GLOBALS['TSFE']->fe_user = $feUser->reveal();

        $this->assertFalse(
            $this->subject->matchCondition([])
        );
    }

    /**
     * @test
     */
    public function matchConditionOnCookieUsageWillReturnTrue()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);
        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();
        /** @var \Prophecy\Prophecy\ObjectProphecy|FrontendUserAuthentication $feUser */
        $feUser = $this->prophesize(FrontendUserAuthentication::class);
        $feUser->getSessionData('mapProviderRequestsAllowedForMaps2')->shouldBeCalled()->willReturn(true);
        $GLOBALS['TSFE']->fe_user = $feUser->reveal();

        $this->assertTrue(
            $this->subject->matchCondition([])
        );
    }
}

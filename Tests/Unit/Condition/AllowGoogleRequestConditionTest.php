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

use JWeiland\Maps2\Condition\AllowGoogleRequestCondition;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Service\GoogleRequestService;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

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
     * @var GoogleRequestService
     */
    protected $googleRequestService;

    /**
     * @var AllowGoogleRequestCondition
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->extConf = new ExtConf();
        $this->googleRequestService = new GoogleRequestService($this->extConf);
        $this->subject = new AllowGoogleRequestCondition($this->googleRequestService);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->extConf, $this->googleRequestService, $this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function matchConditionWithDeactivatedSettingsWillReturnTrue()
    {
        $this->extConf->setExplicitAllowGoogleMaps(0);
        $this->extConf->setExplicitAllowGoogleMapsBySessionOnly(0);
        $this->assertTrue(
            $this->subject->matchCondition([])
        );
    }

    /**
     * @test
     */
    public function matchConditionOnSessionUsageWillReturnFalse()
    {
        $this->extConf->setExplicitAllowGoogleMaps(1);
        $this->extConf->setExplicitAllowGoogleMapsBySessionOnly(1);
        $this->assertFalse(
            $this->subject->matchCondition([])
        );
    }

    /**
     * @test
     */
    public function matchConditionOnSessionUsageWillReturnTrue()
    {
        $this->extConf->setExplicitAllowGoogleMaps(1);
        $this->extConf->setExplicitAllowGoogleMapsBySessionOnly(1);
        $_SESSION['googleRequestsAllowedForMaps2'] = 1;
        $this->assertTrue(
            $this->subject->matchCondition([])
        );
    }

    /**
     * @test
     */
    public function matchConditionOnCookieUsageWillReturnFalseIfTsfeIsNotSet()
    {
        $this->extConf->setExplicitAllowGoogleMaps(1);
        $this->extConf->setExplicitAllowGoogleMapsBySessionOnly(0);
        $this->assertFalse(
            $this->subject->matchCondition([])
        );
    }

    /**
     * @test
     */
    public function matchConditionOnCookieUsageWillReturnFalse()
    {
        $this->extConf->setExplicitAllowGoogleMaps(1);
        $this->extConf->setExplicitAllowGoogleMapsBySessionOnly(0);
        $GLOBALS['TSFE'] = $this->createMock(TypoScriptFrontendController::class);
        /** @var \PHPUnit_Framework_MockObject_MockObject|FrontendUserAuthentication $feUser */
        $feUser = $this->createMock(FrontendUserAuthentication::class);
        $feUser
            ->expects($this->once())
            ->method('getSessionData')
            ->with('googleRequestsAllowedForMaps2')
            ->willReturn(false);
        $GLOBALS['TSFE']->fe_user = $feUser;

        $this->assertFalse(
            $this->subject->matchCondition([])
        );
    }

    /**
     * @test
     */
    public function matchConditionOnCookieUsageWillReturnTrue()
    {
        $this->extConf->setExplicitAllowGoogleMaps(1);
        $this->extConf->setExplicitAllowGoogleMapsBySessionOnly(0);
        $GLOBALS['TSFE'] = $this->createMock(TypoScriptFrontendController::class);
        /** @var \PHPUnit_Framework_MockObject_MockObject|FrontendUserAuthentication $feUser */
        $feUser = $this->createMock(FrontendUserAuthentication::class);
        $feUser
            ->expects($this->once())
            ->method('getSessionData')
            ->with('googleRequestsAllowedForMaps2')
            ->willReturn(true);
        $GLOBALS['TSFE']->fe_user = $feUser;

        $this->assertTrue(
            $this->subject->matchCondition([])
        );
    }
}

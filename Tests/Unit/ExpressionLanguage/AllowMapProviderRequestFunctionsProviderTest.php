<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\ExpressionLanguage;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\ExpressionLanguage\AllowMapProviderRequestFunctionsProvider;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test AllowMapProviderRequestCondition
 */
class AllowMapProviderRequestFunctionsProviderTest extends UnitTestCase
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var AllowMapProviderRequestFunctionsProvider
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->extConf = new ExtConf([]);
        GeneralUtility::setSingletonInstance(ExtConf::class, $this->extConf);

        $this->subject = new AllowMapProviderRequestFunctionsProvider();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->extConf, $this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getFunctionsWithDeactivatedSettingsWillReturnTrue()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(0);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);

        /** @var ExpressionFunction $expressionFunction */
        $expressionFunction = $this->subject->getFunctions()[0];

        self::assertTrue(
            call_user_func($expressionFunction->getEvaluator(), [])
        );
    }

    /**
     * @test
     */
    public function getFunctionsOnSessionUsageWillReturnFalse()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(1);

        /** @var ExpressionFunction $expressionFunction */
        $expressionFunction = $this->subject->getFunctions()[0];

        self::assertFalse(
            call_user_func($expressionFunction->getEvaluator(), [])
        );
    }

    /**
     * @test
     */
    public function getFunctionsOnSessionUsageWillReturnTrue()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(1);

        /** @var ExpressionFunction $expressionFunction */
        $expressionFunction = $this->subject->getFunctions()[0];

        $_SESSION['mapProviderRequestsAllowedForMaps2'] = 1;
        self::assertTrue(
            call_user_func($expressionFunction->getEvaluator(), [])
        );
    }

    /**
     * @test
     */
    public function getFunctionsOnCookieUsageWillReturnFalseIfTsfeIsNotSet()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);

        /** @var ExpressionFunction $expressionFunction */
        $expressionFunction = $this->subject->getFunctions()[0];

        self::assertFalse(
            call_user_func($expressionFunction->getEvaluator(), [])
        );
    }

    /**
     * @test
     */
    public function getFunctionsOnCookieUsageWillReturnFalse()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);

        /** @var ExpressionFunction $expressionFunction */
        $expressionFunction = $this->subject->getFunctions()[0];

        $this->prophesize(TypoScriptFrontendController::class);
        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();

        /** @var FrontendUserAuthentication|ObjectProphecy $feUser */
        $feUser = $this->prophesize(FrontendUserAuthentication::class);
        $feUser->getSessionData('mapProviderRequestsAllowedForMaps2')->shouldBeCalled()->willReturn(false);
        $GLOBALS['TSFE']->fe_user = $feUser->reveal();

        self::assertFalse(
            call_user_func($expressionFunction->getEvaluator(), [])
        );
    }

    /**
     * @test
     */
    public function getFunctionsOnCookieUsageWillReturnTrue()
    {
        $this->extConf->setExplicitAllowMapProviderRequests(1);
        $this->extConf->setExplicitAllowMapProviderRequestsBySessionOnly(0);

        /** @var ExpressionFunction $expressionFunction */
        $expressionFunction = $this->subject->getFunctions()[0];

        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();

        /** @var FrontendUserAuthentication|ObjectProphecy $feUser */
        $feUser = $this->prophesize(FrontendUserAuthentication::class);
        $feUser->getSessionData('mapProviderRequestsAllowedForMaps2')->shouldBeCalled()->willReturn(true);
        $GLOBALS['TSFE']->fe_user = $feUser->reveal();

        self::assertTrue(
            call_user_func($expressionFunction->getEvaluator(), [])
        );
    }
}

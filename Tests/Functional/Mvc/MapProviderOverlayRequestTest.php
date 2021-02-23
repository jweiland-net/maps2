<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Mvc;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Mvc\MapProviderOverlayRequestHandler;
use JWeiland\Maps2\Service\MapProviderRequestService;
use JWeiland\Maps2\Service\MapService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Response;

/**
 * Class MapProviderOverlayRequest
 */
class MapProviderOverlayRequestTest extends FunctionalTestCase
{
    /**
     * @var MapProviderOverlayRequestHandler
     */
    protected $subject;

    /**
     * @var MapProviderRequestService
     */
    protected $mapProviderRequestService;

    /**
     * @var ConfigurationManagerInterface|ObjectProphecy
     */
    protected $configurationManagerProphecy;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var MapService|ObjectProphecy
     */
    protected $mapServiceProphecy;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2'
    ];

    protected function setUp()
    {
        parent::setUp();

        $_SESSION['mapProviderRequestsAllowedForMaps2'] = false;

        $extConf = new ExtConf([]);
        $extConf->setExplicitAllowMapProviderRequests(1);
        $extConf->setExplicitAllowMapProviderRequestsBySessionOnly(1);
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        $this->configurationManagerProphecy = $this->prophesize(ConfigurationManagerInterface::class);
        $this->mapServiceProphecy = $this->prophesize(MapService::class);

        $this->subject = new MapProviderOverlayRequestHandler(
            new MapProviderRequestService(),
            $this->configurationManagerProphecy->reveal(),
            new Response(),
            $this->mapServiceProphecy->reveal()
        );
    }

    protected function tearDown()
    {
        unset(
            $this->subject,
            $this->mapProviderRequestService,
            $this->configurationManagerProphecy,
            $this->response,
            $this->mapServiceProphecy
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function canHandleRequestWillReturnFalseInCliContext()
    {
        Environment::initialize(
            Environment::getContext(),
            true,
            false,
            Environment::getProjectPath(),
            Environment::getPublicPath(),
            Environment::getVarPath(),
            Environment::getConfigPath(),
            Environment::getBackendPath() . '/index.php',
            Environment::isWindows() ? 'WINDOWS' : 'UNIX'
        );

        self::assertFalse(
            $this->subject->canHandleRequest()
        );
    }

    /**
     * @test
     */
    public function canHandleRequestWillReturnFalseWhenExtKeyIsNotMaps2()
    {
        Environment::initialize(
            Environment::getContext(),
            false,
            false,
            Environment::getProjectPath(),
            Environment::getPublicPath(),
            Environment::getVarPath(),
            Environment::getConfigPath(),
            Environment::getBackendPath() . '/index.php',
            Environment::isWindows() ? 'WINDOWS' : 'UNIX'
        );

        $this->configurationManagerProphecy
            ->getConfiguration('Framework')
            ->shouldBeCalled()
            ->willReturn([
                'extensionName' => 'events2'
            ]);

        self::assertFalse(
            $this->subject->canHandleRequest()
        );
    }

    /**
     * @test
     */
    public function canHandleRequestWillReturnTrueWhenExtKeyIsMaps2()
    {
        Environment::initialize(
            Environment::getContext(),
            false,
            false,
            Environment::getProjectPath(),
            Environment::getPublicPath(),
            Environment::getVarPath(),
            Environment::getConfigPath(),
            Environment::getBackendPath() . '/index.php',
            Environment::isWindows() ? 'WINDOWS' : 'UNIX'
        );

        $this->configurationManagerProphecy
            ->getConfiguration('Framework')
            ->shouldBeCalled()
            ->willReturn([
                'extensionName' => 'maps2'
            ]);

        self::assertTrue(
            $this->subject->canHandleRequest()
        );
    }

    /**
     * @test
     */
    public function getPriorityReturnsHigherValueThan100()
    {
        self::assertGreaterThan(
            100,
            $this->subject->getPriority()
        );
    }

    /**
     * @test
     */
    public function handleRequestWillAppendMapFormToContent()
    {
        $testString = 'testHtml';

        $this->mapServiceProphecy
            ->showAllowMapForm()
            ->shouldBeCalled()
            ->willReturn($testString);

        self::assertSame(
            $testString,
            $this->subject->handleRequest()->getContent()
        );
    }
}

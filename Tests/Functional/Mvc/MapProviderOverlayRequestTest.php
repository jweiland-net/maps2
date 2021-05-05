<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Mvc;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Mvc\MapProviderOverlayRequestHandler;
use JWeiland\Maps2\Service\MapService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\RequestBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\EnvironmentService;

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
     * @var MapService|ObjectProphecy
     */
    protected $mapServiceProphecy;

    /**
     * @var RequestBuilder|ObjectProphecy
     */
    protected $requestBuilderProphecy;

    /**
     * @var EnvironmentService|ObjectProphecy
     */
    protected $environmentServiceProphecy;

    /**
     * @var Request|ObjectProphecy
     */
    protected $requestProphecy;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION['mapProviderRequestsAllowedForMaps2'] = false;

        $extConf = new ExtConf([]);
        $extConf->setExplicitAllowMapProviderRequests(1);
        $extConf->setExplicitAllowMapProviderRequestsBySessionOnly(1);
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        $this->mapServiceProphecy = $this->prophesize(MapService::class);
        $this->requestBuilderProphecy = $this->prophesize(RequestBuilder::class);
        $this->requestProphecy = $this->prophesize(Request::class);
        $this->environmentServiceProphecy = $this->prophesize(EnvironmentService::class);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $objectManager->get(MapProviderOverlayRequestHandler::class);
        $this->subject->injectMapService($this->mapServiceProphecy->reveal());
        $this->subject->injectRequestBuilder($this->requestBuilderProphecy->reveal());
        $this->subject->injectEnvironmentService($this->environmentServiceProphecy->reveal());
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->mapServiceProphecy
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function canHandleRequestWillReturnFalseInCliContext(): void
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

        $this->environmentServiceProphecy
            ->isEnvironmentInFrontendMode()
            ->shouldBeCalled()
            ->willReturn(true);

        self::assertFalse(
            $this->subject->canHandleRequest()
        );
    }

    /**
     * @test
     */
    public function canHandleRequestWillReturnFalseWhenExtKeyIsNotMaps2(): void
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

        $this->environmentServiceProphecy
            ->isEnvironmentInFrontendMode()
            ->shouldBeCalled()
            ->willReturn(true);

        $this->requestProphecy
            ->getControllerExtensionKey()
            ->shouldBeCalled()
            ->willReturn('events2');

        $this->requestBuilderProphecy
            ->build()
            ->shouldBeCalled()
            ->willReturn($this->requestProphecy->reveal());

        self::assertFalse(
            $this->subject->canHandleRequest()
        );
    }

    /**
     * @test
     */
    public function canHandleRequestWillReturnTrueWhenExtKeyIsMaps2(): void
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

        $this->environmentServiceProphecy
            ->isEnvironmentInFrontendMode()
            ->shouldBeCalled()
            ->willReturn(true);

        $this->requestProphecy
            ->getControllerExtensionKey()
            ->shouldBeCalled()
            ->willReturn('maps2');

        $this->requestBuilderProphecy
            ->build()
            ->shouldBeCalled()
            ->willReturn($this->requestProphecy->reveal());

        self::assertTrue(
            $this->subject->canHandleRequest()
        );
    }

    /**
     * @test
     */
    public function getPriorityReturnsHigherValueThan100(): void
    {
        self::assertGreaterThan(
            100,
            $this->subject->getPriority()
        );
    }

    /**
     * @test
     */
    public function handleRequestWillAppendMapFormToContent(): void
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

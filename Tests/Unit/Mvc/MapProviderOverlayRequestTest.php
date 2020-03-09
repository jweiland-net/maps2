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
use JWeiland\Maps2\Mvc\MapProviderOverlayRequestHandler;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapProviderRequestService;
use JWeiland\Maps2\Service\MapService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\RequestBuilder;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\EnvironmentService;

/**
 * Class MapProviderOverlayRequest
 */
class MapProviderOverlayRequestTest extends UnitTestCase
{
    /**
     * @var ObjectManager|ObjectProphecy
     */
    protected $objectManager;

    /**
     * @var MapProviderOverlayRequestHandler
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $_SESSION['mapProviderRequestsAllowedForMaps2'] = false;

        $extConf = new ExtConf();
        $extConf->setExplicitAllowMapProviderRequests(1);
        $extConf->setExplicitAllowMapProviderRequestsBySessionOnly(1);
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        $this->objectManager = $this->prophesize(ObjectManager::class);

        $this->subject = new MapProviderOverlayRequestHandler($this->objectManager->reveal());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->googleMapsService, $this->mapProviderRequestService, $this->subject);
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

        $this->assertFalse(
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

        /** @var ConfigurationManagerInterface|ObjectProphecy $configurationManager */
        $configurationManager = $this->prophesize(ConfigurationManagerInterface::class);
        $configurationManager
            ->getConfiguration('Framework')
            ->shouldBeCalled()
            ->willReturn([
                'extensionName' => 'events2'
            ]);
        $this->objectManager
            ->get(ConfigurationManagerInterface::class)
            ->shouldBeCalled()
            ->willReturn($configurationManager->reveal());

        $this->assertFalse(
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

        /** @var ConfigurationManagerInterface|ObjectProphecy $configurationManager */
        $configurationManager = $this->prophesize(ConfigurationManagerInterface::class);
        $configurationManager
            ->getConfiguration('Framework')
            ->shouldBeCalled()
            ->willReturn([
                'extensionName' => 'maps2'
            ]);
        $this->objectManager
            ->get(ConfigurationManagerInterface::class)
            ->shouldBeCalled()
            ->willReturn($configurationManager->reveal());

        $this->assertTrue(
            $this->subject->canHandleRequest()
        );
    }

    /**
     * @test
     */
    public function getPriorityReturnsHigherValueThan100()
    {
        $this->assertGreaterThan(
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

        $response = new Response();
        $this->objectManager->get(Response::class)->shouldBeCalled()->willReturn($response);

        /** @var MapService|ObjectProphecy $mapService */
        $mapService = $this->prophesize(MapService::class);
        $mapService->showAllowMapForm()->shouldBeCalled()->willReturn($testString);
        GeneralUtility::addInstance(MapService::class, $mapService->reveal());

        $this->assertSame(
            $testString,
            $this->subject->handleRequest()->getContent()
        );
    }
}

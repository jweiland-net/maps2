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
use JWeiland\Maps2\Service\MapService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Test MapService
 */
class MapServiceTest extends UnitTestCase
{
    /**
     * @var ObjectManager|ObjectProphecy
     */
    protected $objectManagerProphecy;

    /**
     * @var EnvironmentService|ObjectProphecy
     */
    protected $environmentServiceProphecy;

    /**
     * @var ConfigurationManagerInterface|ObjectProphecy
     */
    protected $configurationManagerProphecy;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var MapService
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->objectManagerProphecy = $this->prophesize(ObjectManager::class);
        $this->environmentServiceProphecy = $this->prophesize(EnvironmentService::class);
        $this->environmentServiceProphecy
            ->isEnvironmentInFrontendMode()
            ->shouldBeCalled()
            ->willReturn(true);

        $this->configurationManagerProphecy = $this->prophesize(ConfigurationManager::class);
        $this->objectManagerProphecy
            ->get(ConfigurationManagerInterface::class)
            ->shouldBeCalled()
            ->willReturn($this->configurationManagerProphecy->reveal());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->objectManagerProphecy, $this->configurationManagerProphecy, $this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function showAllowMapFormWithMissingMapProviderWillCreateFlashMessage()
    {
        $testString = 'testHtml';

        $extConf = new ExtConf();
        $extConf->setAllowMapTemplatePath('typo3conf/ext/maps/Resources/Private/Templates/AllowMapForm.html');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2'
            )
            ->shouldBeCalled()
            ->willReturn($this->settings);

        $flashMessage = new FlashMessage(
            'You have forgotten...',
            'Missing static template',
            AbstractMessage::ERROR
        );
        GeneralUtility::addInstance(FlashMessage::class, $flashMessage);

        /** @var FlashMessageQueue|ObjectProphecy $flashMessageQueueProphecy */
        $flashMessageQueueProphecy = $this->prophesize(FlashMessageQueue::class);
        $flashMessageQueueProphecy
            ->enqueue($flashMessage)
            ->shouldBeCalled();

        /** @var FlashMessageService|ObjectProphecy $flashMessageServiceProphecy */
        $flashMessageServiceProphecy = $this->prophesize(FlashMessageService::class);
        $flashMessageServiceProphecy
            ->getMessageQueueByIdentifier('maps2.allowMap')
            ->shouldBeCalled()
            ->willReturn($flashMessageQueueProphecy->reveal());
        GeneralUtility::setSingletonInstance(FlashMessageService::class, $flashMessageServiceProphecy->reveal());

        /** @var StandaloneView|ObjectProphecy $viewProphecy */
        $viewProphecy = $this->prophesize(StandaloneView::class);
        $viewProphecy->setTemplatePathAndFilename(Argument::any())->shouldBeCalled();
        $viewProphecy->assign('settings', $this->settings)->shouldBeCalled();
        $viewProphecy->assign('requestUri', 'MyCoolRequestUri')->shouldBeCalled();
        $viewProphecy->render()->shouldBeCalled()->willReturn($testString);
        GeneralUtility::addInstance(StandaloneView::class, $viewProphecy->reveal());

        /** @var UriBuilder $uriBuilderProphecy */
        $uriBuilderProphecy = $this->prophesize(UriBuilder::class);
        $uriBuilderProphecy->reset()->shouldBeCalled()->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy->setAddQueryString(true)->shouldBeCalled()->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy
            ->setArguments([
                'tx_maps2_maps2' => [
                    'mapProviderRequestsAllowedForMaps2' => 1
                ]
            ])
            ->shouldBeCalled()
            ->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy
            ->setArgumentsToBeExcludedFromQueryString(['cHash'])
            ->shouldBeCalled()
            ->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy
            ->build()
            ->shouldBeCalled()
            ->willReturn('MyCoolRequestUri');
        $this->objectManagerProphecy
            ->get(UriBuilder::class)
            ->shouldBeCalled()
            ->willReturn($uriBuilderProphecy->reveal());

        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManagerProphecy->reveal());
        GeneralUtility::setSingletonInstance(EnvironmentService::class, $this->environmentServiceProphecy->reveal());
        $this->subject = new MapService();

        $this->assertSame(
            $testString,
            $this->subject->showAllowMapForm()
        );
    }

    /**
     * @test
     */
    public function showAllowMapFormWithEmptyMapProviderWillCreateFlashMessage()
    {
        $testString = 'testHtml';

        $extConf = new ExtConf();
        $extConf->setAllowMapTemplatePath('typo3conf/ext/maps/Resources/Private/Templates/AllowMapForm.html');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        $this->settings['mapProvider'] = '';
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2'
            )
            ->shouldBeCalled()
            ->willReturn($this->settings);

        $flashMessage = new FlashMessage(
            'You have forgotten...',
            'Missing static template',
            AbstractMessage::ERROR
        );
        GeneralUtility::addInstance(FlashMessage::class, $flashMessage);

        /** @var FlashMessageQueue|ObjectProphecy $flashMessageQueueProphecy */
        $flashMessageQueueProphecy = $this->prophesize(FlashMessageQueue::class);
        $flashMessageQueueProphecy
            ->enqueue($flashMessage)
            ->shouldBeCalled();

        /** @var FlashMessageService|ObjectProphecy $flashMessageServiceProphecy */
        $flashMessageServiceProphecy = $this->prophesize(FlashMessageService::class);
        $flashMessageServiceProphecy
            ->getMessageQueueByIdentifier('maps2.allowMap')
            ->shouldBeCalled()
            ->willReturn($flashMessageQueueProphecy->reveal());
        GeneralUtility::setSingletonInstance(FlashMessageService::class, $flashMessageServiceProphecy->reveal());

        /** @var StandaloneView|ObjectProphecy $viewProphecy */
        $viewProphecy = $this->prophesize(StandaloneView::class);
        $viewProphecy->setTemplatePathAndFilename(Argument::any())->shouldBeCalled();
        $viewProphecy->assign('settings', $this->settings)->shouldBeCalled();
        $viewProphecy->assign('requestUri', 'MyCoolRequestUri')->shouldBeCalled();
        $viewProphecy->render()->shouldBeCalled()->willReturn($testString);
        GeneralUtility::addInstance(StandaloneView::class, $viewProphecy->reveal());

        /** @var UriBuilder $uriBuilderProphecy */
        $uriBuilderProphecy = $this->prophesize(UriBuilder::class);
        $uriBuilderProphecy->reset()->shouldBeCalled()->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy->setAddQueryString(true)->shouldBeCalled()->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy
            ->setArguments([
                'tx_maps2_maps2' => [
                    'mapProviderRequestsAllowedForMaps2' => 1
                ]
            ])
            ->shouldBeCalled()
            ->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy
            ->setArgumentsToBeExcludedFromQueryString(['cHash'])
            ->shouldBeCalled()
            ->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy
            ->build()
            ->shouldBeCalled()
            ->willReturn('MyCoolRequestUri');
        $this->objectManagerProphecy
            ->get(UriBuilder::class)
            ->shouldBeCalled()
            ->willReturn($uriBuilderProphecy->reveal());

        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManagerProphecy->reveal());
        GeneralUtility::setSingletonInstance(EnvironmentService::class, $this->environmentServiceProphecy->reveal());
        $this->subject = new MapService();

        $this->assertSame(
            $testString,
            $this->subject->showAllowMapForm()
        );
    }

    /**
     * @test
     */
    public function showAllowMapFormWithMapProviderWillAssignVariablesToView()
    {
        $testString = 'testHtml';

        $extConf = new ExtConf();
        $extConf->setAllowMapTemplatePath('typo3conf/ext/maps/Resources/Private/Templates/AllowMapForm.html');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        $this->settings['mapProvider'] = 'gm';
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2'
            )
            ->shouldBeCalled()
            ->willReturn($this->settings);

        /** @var StandaloneView|ObjectProphecy $viewProphecy */
        $viewProphecy = $this->prophesize(StandaloneView::class);
        $viewProphecy->setTemplatePathAndFilename(Argument::any())->shouldBeCalled();
        $viewProphecy->assign('settings', $this->settings)->shouldBeCalled();
        $viewProphecy->assign('requestUri', 'MyCoolRequestUri')->shouldBeCalled();
        $viewProphecy->render()->shouldBeCalled()->willReturn($testString);
        GeneralUtility::addInstance(StandaloneView::class, $viewProphecy->reveal());

        /** @var UriBuilder $uriBuilderProphecy */
        $uriBuilderProphecy = $this->prophesize(UriBuilder::class);
        $uriBuilderProphecy->reset()->shouldBeCalled()->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy->setAddQueryString(true)->shouldBeCalled()->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy
            ->setArguments([
                'tx_maps2_maps2' => [
                    'mapProviderRequestsAllowedForMaps2' => 1
                ]
            ])
            ->shouldBeCalled()
            ->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy
            ->setArgumentsToBeExcludedFromQueryString(['cHash'])
            ->shouldBeCalled()
            ->willReturn($uriBuilderProphecy->reveal());
        $uriBuilderProphecy
            ->build()
            ->shouldBeCalled()
            ->willReturn('MyCoolRequestUri');
        $this->objectManagerProphecy
            ->get(UriBuilder::class)
            ->shouldBeCalled()
            ->willReturn($uriBuilderProphecy->reveal());

        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManagerProphecy->reveal());
        GeneralUtility::setSingletonInstance(EnvironmentService::class, $this->environmentServiceProphecy->reveal());
        $this->subject = new MapService();

        $this->assertSame(
            $testString,
            $this->subject->showAllowMapForm()
        );
    }

    /**
     * @test
     */
    public function getMapProviderWillReturnOsm()
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2'
            )
            ->shouldBeCalled()
            ->willReturn($this->settings);

        $extConf = new ExtConf();
        $extConf->setMapProvider('osm');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManagerProphecy->reveal());
        GeneralUtility::setSingletonInstance(EnvironmentService::class, $this->environmentServiceProphecy->reveal());
        $this->subject = new MapService();

        $this->assertSame(
            'osm',
            $this->subject->getMapProvider()
        );
    }

    /**
     * @test
     */
    public function getMapProviderWillReturnGm()
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2'
            )
            ->shouldBeCalled()
            ->willReturn($this->settings);

        $extConf = new ExtConf();
        $extConf->setMapProvider('gm');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManagerProphecy->reveal());
        GeneralUtility::setSingletonInstance(EnvironmentService::class, $this->environmentServiceProphecy->reveal());
        $this->subject = new MapService();

        $this->assertSame(
            'gm',
            $this->subject->getMapProvider()
        );
    }

    /**
     * @test
     */
    public function getMapProviderWillReturnDefaultMapProvider()
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2'
            )
            ->shouldBeCalled()
            ->willReturn($this->settings);

        $extConf = new ExtConf();
        $extConf->setMapProvider('both');
        $extConf->setDefaultMapProvider('osm');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManagerProphecy->reveal());
        GeneralUtility::setSingletonInstance(EnvironmentService::class, $this->environmentServiceProphecy->reveal());
        $this->subject = new MapService();

        $this->assertSame(
            'osm',
            $this->subject->getMapProvider()
        );
    }

    /**
     * @test
     */
    public function getMapProviderWillReturnMapProviderAsStringFromDatabase()
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2'
            )
            ->shouldBeCalled()
            ->willReturn($this->settings);

        $extConf = new ExtConf();
        $extConf->setMapProvider('both');
        $extConf->setDefaultMapProvider('osm');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManagerProphecy->reveal());
        GeneralUtility::setSingletonInstance(EnvironmentService::class, $this->environmentServiceProphecy->reveal());
        $this->subject = new MapService();

        $this->assertSame(
            'gm',
            $this->subject->getMapProvider([
                'map_provider' => 'gm'
            ])
        );
    }

    /**
     * @test
     */
    public function getMapProviderWillReturnMapProviderAsArrayFromDatabase()
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2'
            )
            ->shouldBeCalled()
            ->willReturn($this->settings);

        $extConf = new ExtConf();
        $extConf->setMapProvider('both');
        $extConf->setDefaultMapProvider('osm');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManagerProphecy->reveal());
        GeneralUtility::setSingletonInstance(EnvironmentService::class, $this->environmentServiceProphecy->reveal());
        $this->subject = new MapService();

        $this->assertSame(
            'gm',
            $this->subject->getMapProvider([
                'map_provider' => [
                    0 => 'gm'
                ]
            ])
        );
    }

    /**
     * @test
     */
    public function getMapProviderWillReturnDefaultMapProviderFromDatabaseIfEmpty()
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2'
            )
            ->shouldBeCalled()
            ->willReturn($this->settings);

        $extConf = new ExtConf();
        $extConf->setMapProvider('both');
        $extConf->setDefaultMapProvider('gm');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManagerProphecy->reveal());
        GeneralUtility::setSingletonInstance(EnvironmentService::class, $this->environmentServiceProphecy->reveal());
        $this->subject = new MapService();

        $this->assertSame(
            'gm',
            $this->subject->getMapProvider([
                'map_provider' => ''
            ])
        );
    }

    /**
     * @test
     */
    public function getMapProviderWillReturnDefaultMapProviderFromDatabaseIfMissing()
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2'
            )
            ->shouldBeCalled()
            ->willReturn($this->settings);

        $extConf = new ExtConf();
        $extConf->setMapProvider('both');
        $extConf->setDefaultMapProvider('gm');
        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManagerProphecy->reveal());
        GeneralUtility::setSingletonInstance(EnvironmentService::class, $this->environmentServiceProphecy->reveal());
        $this->subject = new MapService();

        $this->assertSame(
            'gm',
            $this->subject->getMapProvider([
                'uid' => 123
            ])
        );
    }
}

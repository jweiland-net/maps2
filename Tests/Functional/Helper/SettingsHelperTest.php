<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Helper;

use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Helper\SettingsHelper;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Test StoragePidHelper
 */
class SettingsHelperTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected SettingsHelper $subject;

    /**
     * @var ConfigurationManagerInterface|ObjectProphecy
     */
    protected $configurationManagerProphecy;

    protected array $typoScriptSettings = [
        'settings' => [
            'mapProvider' => 'gm',
            'mapTypeControl' => '1',
            'scaleControl' => '1',
            'streetViewControl' => '0',
            'fullscreenMapControl' => '1',
            'zoom' => '10',
            'zoomControl' => '1',
            'overlay' => [
                'link' => [
                    'addSection' => '1'
                ]
            ],
            'infoWindowContentTemplatePath' => '',
            'infoWindow' => [
                'image' => [
                    'width' => '150c',
                    'height' => '150c',
                ]
            ]
        ]
    ];

    protected array $mergedScriptSettings = [
        'mapWidth' => '100%',
        'mapHeight' => '300',
        'mapProvider' => 'gm',
        'mapTypeControl' => '1',
        'scaleControl' => '1',
        'streetViewControl' => '1',
        'fullscreenMapControl' => '1',
        'zoom' => '12',
        'forceZoom' => '0',
        'zoomControl' => '1',
        'activateScrollWheel' => '1',
        'fullScreenControl' => '1',
        'overlay' => [
            'link' => [
                'addSection' => '1'
            ]
        ],
        'infoWindowContentTemplatePath' => '',
        'infoWindow' => [
            'image' => [
                'width' => '150c',
                'height' => '150c',
            ]
        ]
    ];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationManagerProphecy = $this->prophesize(ConfigurationManagerInterface::class);

        $this->subject = new SettingsHelper(
            $this->configurationManagerProphecy->reveal()
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->configurationManagerProphecy
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getMergedSettingsWillNotChangeAnySettings(): void
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'maps2',
                'invalid'
            )
            ->shouldBeCalled()
            ->willReturn($this->typoScriptSettings);

        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
            )
            ->shouldBeCalled()
            ->willReturn($this->mergedScriptSettings);

        self::assertSame(
            $this->mergedScriptSettings,
            $this->subject->getMergedSettings()
        );
    }

    /**
     * @test
     */
    public function getMergedSettingsWillOverrideEmptyInfoWindowContentTemplateWithTypoScriptValue(): void
    {
        $typoScriptSettings = $this->typoScriptSettings;
        $typoScriptSettings['settings']['infoWindowContentTemplatePath']
            = 'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html';

        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'maps2',
                'invalid'
            )
            ->shouldBeCalled()
            ->willReturn($typoScriptSettings);

        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
            )
            ->shouldBeCalled()
            ->willReturn($this->mergedScriptSettings);

        self::assertSame(
            'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html',
            $this->subject->getMergedSettings()['infoWindowContentTemplatePath']
        );
    }

    /**
     * @test
     */
    public function getMergedSettingsWithDeactivatedFullscreenMapControlWillKeepFlexFormSetting(): void
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'maps2',
                'invalid'
            )
            ->shouldBeCalled()
            ->willReturn($this->typoScriptSettings);

        $mergedSettings = $this->mergedScriptSettings;
        $mergedSettings['fullscreenMapControl'] = '0';

        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
            )
            ->shouldBeCalled()
            ->willReturn($mergedSettings);

        self::assertSame(
            '0',
            $this->subject->getMergedSettings()['fullscreenMapControl']
        );
    }

    /**
     * @test
     */
    public function getMergedSettingsWithActivatedStreetViewControlWillKeepFlexFormSetting(): void
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'maps2',
                'invalid'
            )
            ->shouldBeCalled()
            ->willReturn($this->typoScriptSettings);

        $mergedSettings = $this->mergedScriptSettings;
        $mergedSettings['streetViewControl'] = '1';

        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
            )
            ->shouldBeCalled()
            ->willReturn($mergedSettings);

        self::assertSame(
            '1',
            $this->subject->getMergedSettings()['streetViewControl']
        );
    }
}

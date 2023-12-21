<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Helper;

use JWeiland\Maps2\Helper\SettingsHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test StoragePidHelper
 */
class SettingsHelperTest extends FunctionalTestCase
{
    protected SettingsHelper $subject;

    /**
     * @var ConfigurationManagerInterface|MockObject
     */
    protected $configurationManagerMock;

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
                    'addSection' => '1',
                ],
            ],
            'infoWindowContentTemplatePath' => '',
            'infoWindow' => [
                'image' => [
                    'width' => '150c',
                    'height' => '150c',
                ],
            ],
        ],
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
                'addSection' => '1',
            ],
        ],
        'infoWindowContentTemplatePath' => '',
        'infoWindow' => [
            'image' => [
                'width' => '150c',
                'height' => '150c',
            ],
        ],
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationManagerMock = $this->createMock(ConfigurationManagerInterface::class);

        $this->subject = new SettingsHelper($this->configurationManagerMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->configurationManagerMock
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getMergedSettingsWillNotChangeAnySettings(): void
    {
        $this->configurationManagerMock
            ->expects(self::atLeastOnce())
            ->method('getConfiguration')
            ->willReturnMap([
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                    'maps2',
                    'invalid',
                    $this->typoScriptSettings,
                ],
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                    null,
                    null,
                    $this->mergedScriptSettings,
                ],
            ]);

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

        $this->configurationManagerMock
            ->expects(self::atLeastOnce())
            ->method('getConfiguration')
            ->willReturnMap([
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                    'maps2',
                    'invalid',
                    $typoScriptSettings,
                ],
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                    null,
                    null,
                    $this->mergedScriptSettings,
                ],
            ]);

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
        $mergedSettings = $this->mergedScriptSettings;
        $mergedSettings['fullscreenMapControl'] = '0';

        $this->configurationManagerMock
            ->expects(self::atLeastOnce())
            ->method('getConfiguration')
            ->willReturnMap([
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                    'maps2',
                    'invalid',
                    $this->typoScriptSettings,
                ],
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                    null,
                    null,
                    $mergedSettings,
                ],
            ]);

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
        $mergedSettings = $this->mergedScriptSettings;
        $mergedSettings['streetViewControl'] = '1';

        $this->configurationManagerMock
            ->expects(self::atLeastOnce())
            ->method('getConfiguration')
            ->willReturnMap([
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                    'maps2',
                    'invalid',
                    $this->typoScriptSettings,
                ],
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                    null,
                    null,
                    $mergedSettings,
                ],
            ]);

        self::assertSame(
            '1',
            $this->subject->getMergedSettings()['streetViewControl']
        );
    }
}

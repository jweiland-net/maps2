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
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test StoragePidHelper
 */
class SettingsHelperTest extends FunctionalTestCase
{
    protected SettingsHelper $subject;

    protected array $typoScriptSettings = [
        'plugin.' => [
            'tx_maps2.' => [
                'settings.' => [
                    'mapProvider' => 'gm',
                    'mapTypeControl' => '1',
                    'scaleControl' => '1',
                    'streetViewControl' => '0',
                    'fullscreenMapControl' => '1',
                    'zoom' => '10',
                    'zoomControl' => '1',
                    'overlay.' => [
                        'link.' => [
                            'addSection' => '1',
                        ],
                    ],
                    'infoWindowContentTemplatePath' => '',
                    'infoWindow.' => [
                        'image.' => [
                            'width' => '150c',
                            'height' => '150c',
                        ],
                    ],
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

        $this->subject = new SettingsHelper(
            $this->get(TypoScriptService::class),
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    #[Test]
    public function getMergedSettingsWillNotChangeAnySettings(): void
    {
        $typoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $typoScript->setSetupArray($this->typoScriptSettings);
        $request = (new ServerRequest())->withAttribute('frontend.typoscript', $typoScript);

        self::assertSame(
            $this->mergedScriptSettings,
            $this->subject->restoreTypoScriptDefaultsForEmptyFlexFormSettings(
                $this->mergedScriptSettings,
                $request,
            ),
        );
    }

    #[Test]
    public function getMergedSettingsWillOverrideEmptyInfoWindowContentTemplateWithTypoScriptValue(): void
    {
        $typoScriptSettings = $this->typoScriptSettings;
        $typoScriptSettings['plugin.']['tx_maps2.']['settings.']['infoWindowContentTemplatePath']
            = 'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html';

        $typoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $typoScript->setSetupArray($typoScriptSettings);
        $request = (new ServerRequest())->withAttribute('frontend.typoscript', $typoScript);

        self::assertSame(
            'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html',
            $this->subject->restoreTypoScriptDefaultsForEmptyFlexFormSettings(
                $this->mergedScriptSettings,
                $request,
            )['infoWindowContentTemplatePath'],
        );
    }

    #[Test]
    public function getMergedSettingsWithDeactivatedFullscreenMapControlWillKeepFlexFormSetting(): void
    {
        $typoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $typoScript->setSetupArray($this->typoScriptSettings);
        $request = (new ServerRequest())->withAttribute('frontend.typoscript', $typoScript);

        $mergedSettings = $this->mergedScriptSettings;
        $mergedSettings['fullscreenMapControl'] = '0';

        self::assertSame(
            '0',
            $this->subject->restoreTypoScriptDefaultsForEmptyFlexFormSettings(
                $mergedSettings,
                $request,
            )['fullscreenMapControl'],
        );
    }

    #[Test]
    public function getMergedSettingsWithActivatedStreetViewControlWillKeepFlexFormSetting(): void
    {
        $typoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $typoScript->setSetupArray($this->typoScriptSettings);
        $request = (new ServerRequest())->withAttribute('frontend.typoscript', $typoScript);

        $mergedSettings = $this->mergedScriptSettings;
        $mergedSettings['streetViewControl'] = '1';

        self::assertSame(
            '1',
            $this->subject->restoreTypoScriptDefaultsForEmptyFlexFormSettings(
                $mergedSettings,
                $request,
            )['streetViewControl'],
        );
    }
}

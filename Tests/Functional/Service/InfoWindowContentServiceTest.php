<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Service;

use JWeiland\Maps2\Event\RenderInfoWindowContentEvent;
use JWeiland\Maps2\Service\InfoWindowContentService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Settings\Settings;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test MapService
 */
class InfoWindowContentServiceTest extends FunctionalTestCase
{
    protected InfoWindowContentService $subject;

    protected ViewFactoryInterface $viewFactory;

    protected ContentDataProcessor $contentDataProcessor;

    protected TypoScriptService $typoScriptService;

    protected EventDispatcherInterface|MockObject $eventDispatcherMock;

    protected array $coreExtensionsToLoad = [
        'extensionmanager',
        'reactions',
    ];

    protected array $testExtensionsToLoad = [
        'sjbr/static-info-tables',
        'jweiland/maps2',
        'jweiland/events2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_events2_domain_model_location.csv');

        $this->viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
        $this->contentDataProcessor = GeneralUtility::makeInstance(ContentDataProcessor::class);
        $this->typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcher::class);

        $this->subject = new InfoWindowContentService(
            $this->viewFactory,
            $this->contentDataProcessor,
            $this->typoScriptService,
            $this->eventDispatcherMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->eventDispatcherMock,
            $this->typoScriptService,
            $this->contentDataProcessor,
            $this->viewFactory,
        );

        parent::tearDown();
    }

    #[Test]
    public function renderWithoutPluginPathWillReturnError(): void
    {
        $poiCollectionRecord = [];

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setConfigArray([]);
        $frontendTypoScript->setSetupArray([]);

        $request = new ServerRequest('https://www.example.com/', 'GET');
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('frontend.typoscript', $frontendTypoScript);

        $this->eventDispatcherMock
            ->expects(self::atLeastOnce())
            ->method('dispatch')
            ->willReturn(new RenderInfoWindowContentEvent($poiCollectionRecord, $request));

        self::assertStringStartsWith(
            'ERROR: Path at plugin.tx_maps2 not found',
            $this->subject->render($poiCollectionRecord, $request),
        );
    }

    #[Test]
    public function renderWithEmptyPluginPathWillReturnError(): void
    {
        $poiCollectionRecord = [];

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setConfigArray([]);
        $frontendTypoScript->setSetupArray([
            'plugin.' => [
                'tx_maps2.' => [],
            ],
        ]);

        $request = new ServerRequest('https://www.example.com/', 'GET');
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('frontend.typoscript', $frontendTypoScript);

        $this->eventDispatcherMock
            ->expects(self::atLeastOnce())
            ->method('dispatch')
            ->willReturn(new RenderInfoWindowContentEvent($poiCollectionRecord, $request));

        self::assertStringStartsWith(
            'ERROR: Path at plugin.tx_maps2 not found',
            $this->subject->render($poiCollectionRecord, $request),
        );
    }

    #[Test]
    public function renderWithEmptySiteSettingsWillReturnError(): void
    {
        $poiCollectionRecord = [];

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setConfigArray([]);
        $frontendTypoScript->setSetupArray([
            'plugin.' => [
                'tx_maps2.' => [
                    'settings.' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ]);

        $site = new Site(
            'maps2',
            1,
            [],
            new SiteSettings(new Settings([]), [], [])
        );

        $request = new ServerRequest('https://www.example.com/', 'GET');
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('frontend.typoscript', $frontendTypoScript);
        $request = $request->withAttribute('site', $site);

        $this->eventDispatcherMock
            ->expects(self::atLeastOnce())
            ->method('dispatch')
            ->willReturn(new RenderInfoWindowContentEvent($poiCollectionRecord, $request));

        self::assertStringStartsWith(
            'Error: Missing site settings',
            $this->subject->render($poiCollectionRecord, $request),
        );
    }

    #[Test]
    public function renderWithMissingEvents2SiteSettingsWillReturnError(): void
    {
        $poiCollectionRecord = [];

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setConfigArray([]);
        $frontendTypoScript->setSetupArray([
            'plugin.' => [
                'tx_maps2.' => [
                    'settings.' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ]);

        $site = new Site(
            'maps2',
            1,
            [],
            new SiteSettings(
                new Settings([
                    'maps2' => [],
                ]),
                [],
                [],
            )
        );

        $request = new ServerRequest('https://www.example.com/', 'GET');
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('frontend.typoscript', $frontendTypoScript);
        $request = $request->withAttribute('site', $site);

        $this->eventDispatcherMock
            ->expects(self::atLeastOnce())
            ->method('dispatch')
            ->willReturn(new RenderInfoWindowContentEvent($poiCollectionRecord, $request));

        self::assertStringStartsWith(
            'Error: Missing site settings',
            $this->subject->render($poiCollectionRecord, $request),
        );
    }

    #[Test]
    public function renderWillReturnInfoWindowContent(): void
    {
        $poiCollectionRecord = [
            'title' => 'jweiland.net',
            'address' => 'Echterdinger Straße 57, 70794 Filderstadt, Germany',
            'info_window_content' => 'Welcome',
        ];

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setConfigArray([]);
        $frontendTypoScript->setSetupArray([
            'plugin.' => [
                'tx_maps2.' => [
                    'view.' => [
                        'layoutRootPaths.' => [
                            '0' => 'EXT:maps2/Resources/Private/Layouts/'
                        ],
                        'partialRootPaths.' => [
                            '0' => 'EXT:maps2/Resources/Private/Partials/'
                        ],
                        'templateRootPaths.' => [
                            '0' => 'EXT:maps2/Resources/Private/Templates/'
                        ],
                    ],
                    'settings.' => [
                        'infoWindowContent.' => [
                            'view.' => [
                                'dataProcessing.' => [
                                    '10' => 'files',
                                    '10.' => [
                                        'as' => 'infoWindowImages',
                                        'references.' => [
                                            'fieldName' => 'info_window_images',
                                            'table' => 'tx_maps2_domain_model_poicollection',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $site = new Site(
            'maps2',
            1,
            [],
            new SiteSettings(
                new Settings([
                    'maps2' => [
                        'infoWindowContent' => [
                            'templatePath' => 'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html',
                        ],
                    ],
                ]),
                [],
                [],
            )
        );

        $request = new ServerRequest('https://www.example.com/', 'GET');
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('frontend.typoscript', $frontendTypoScript);
        $request = $request->withAttribute('site', $site);

        $GLOBALS['TYPO3_REQUEST'] = $request;

        $this->eventDispatcherMock
            ->expects(self::atLeastOnce())
            ->method('dispatch')
            ->willReturn(new RenderInfoWindowContentEvent($poiCollectionRecord, $request));

        $infoWindowContent = $this->subject->render($poiCollectionRecord, $request);

        self::assertStringContainsString(
            '<strong>jweiland.net</strong>',
            $infoWindowContent,
        );
        self::assertStringContainsString(
            'Echterdinger Straße 57',
            $infoWindowContent,
        );
        self::assertStringContainsString(
            '70794 Filderstadt',
            $infoWindowContent,
        );
        self::assertStringContainsString(
            'Welcome',
            $infoWindowContent,
        );
    }
}

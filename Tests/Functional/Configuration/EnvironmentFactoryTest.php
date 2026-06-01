<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Configuration;

use JWeiland\Maps2\Configuration\EnvironmentFactory;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\LinkHelper;
use JWeiland\Maps2\Helper\SettingsHelper;
use JWeiland\Maps2\Tests\Functional\Traits\SetUpFrontendSiteTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional test for EnvironmentFactory
 */
class EnvironmentFactoryTest extends FunctionalTestCase
{
    use SetUpFrontendSiteTrait;
    protected array $coreExtensionsToLoad = [];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUpFrontendSiteWithBase(int $pageId, string $base): void
    {
        $languages = [
            0 => [
                'title' => 'English',
                'enabled' => true,
                'languageId' => 0,
                'base' => $base,
                'locale' => 'en_US.UTF-8',
                'navigationTitle' => '',
                'flag' => 'us',
            ],
        ];

        $configuration = [
            'rootPageId' => $pageId,
            'base' => $base,
            'languages' => $languages,
            'errorHandling' => [],
            'routes' => [],
        ];

        GeneralUtility::mkdir_deep($this->instancePath . '/typo3conf/sites/testing/');
        $yamlFileContents = \Symfony\Component\Yaml\Yaml::dump($configuration, 99, 2);
        $fileName = $this->instancePath . '/typo3conf/sites/testing/config.yaml';
        GeneralUtility::writeFile($fileName, $yamlFileContents);

        /** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
        $cacheManager->flushCaches();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    #[Test]
    public function buildEnvironmentBuildsCorrectState(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->setUpFrontendSiteWithBase(1, 'https://www.example.com/');
        $this->setUpFrontendRootPage(1);

        $extConf = new ExtConf();

        // Use native LinkHelper with SiteFinder from DI container
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $linkHelper = new LinkHelper($siteFinder);

        // Mock SettingsHelper as requested
        /** @var SettingsHelper|MockObject $settingsHelper */
        $settingsHelper = $this->createMock(SettingsHelper::class);
        $settingsHelper->expects($this->once())
            ->method('getMergedSettings')
            ->willReturn(['some' => 'settings']);
        $settingsHelper->expects($this->once())
            ->method('getPreparedSettings')
            ->with(['some' => 'settings'])
            ->willReturn(['prepared' => 'settings']);

        // Set up native ContentObjectRenderer and record data
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObject->data = [
            'uid' => 42,
            'pi_flexform' => 'should be removed',
            'l18n_diffsource' => 'should be removed',
            'header' => 'My Map',
        ];

        // Retrieve the booted site from SiteFinder
        $site = $siteFinder->getSiteByPageId(1);

        $routing = new PageArguments(13, 'route', []);

        // Build a real ServerRequest and register it in $GLOBALS['TYPO3_REQUEST']
        $request = (new ServerRequest('https://www.example.com/'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('currentContentObject', $contentObject)
            ->withAttribute('site', $site)
            ->withAttribute('routing', $routing);

        $GLOBALS['TYPO3_REQUEST'] = $request;

        $factory = new EnvironmentFactory($extConf, $linkHelper, $settingsHelper);
        $environment = $factory->buildEnvironment($request);

        self::assertSame(['prepared' => 'settings'], $environment->getSettings());
        // LinkHelper should natively generate the URL using the booted Site base and routing page ID
        self::assertStringContainsString('default-parent', $environment->getAjaxUrl());
        self::assertSame(13, $environment->getId());

        $expectedRecord = [
            'uid' => 42,
            'header' => 'My Map',
        ];
        self::assertSame($expectedRecord, $environment->getContentRecord());
    }
}

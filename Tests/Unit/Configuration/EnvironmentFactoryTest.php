<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Unit\Configuration;

use JWeiland\Maps2\Configuration\EnvironmentFactory;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\LinkHelper;
use JWeiland\Maps2\Helper\SettingsHelper;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ClassSchema;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class EnvironmentFactoryTest
 */
class EnvironmentFactoryTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    #[Test]
    public function buildEnvironmentBuildsCorrectState(): void
    {
        $reflectionService = $this->createMock(ReflectionService::class);
        $classSchema = $this->createMock(ClassSchema::class);
        $classSchema->expects($this->any())
            ->method('getProperties')
            ->willReturn([]);
        $reflectionService->expects($this->any())
            ->method('getClassSchema')
            ->willReturn($classSchema);
        GeneralUtility::setSingletonInstance(ReflectionService::class, $reflectionService);

        $extConf = new ExtConf();

        $linkHelper = $this->createMock(LinkHelper::class);
        $linkHelper->expects($this->once())
            ->method('buildUriToCurrentPage')
            ->willReturn('/ajax-uri');

        $settingsHelper = $this->createMock(SettingsHelper::class);
        $settingsHelper->expects($this->once())
            ->method('getMergedSettings')
            ->willReturn(['some' => 'settings']);
        $settingsHelper->expects($this->once())
            ->method('getPreparedSettings')
            ->with(['some' => 'settings'])
            ->willReturn(['prepared' => 'settings']);

        $contentObject = $this->createMock(ContentObjectRenderer::class);
        $contentObject->data = [
            'uid' => 42,
            'pi_flexform' => 'should be removed',
            'l18n_diffsource' => 'should be removed',
            'header' => 'My Map',
        ];

        $routing = $this->createMock(PageArguments::class);
        $routing->expects($this->any())
            ->method('getPageId')
            ->willReturn(123);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
            ->method('getAttribute')
            ->willReturnMap([
                ['currentContentObject', $contentObject],
                ['routing', $routing],
            ]);

        $factory = new EnvironmentFactory($extConf, $linkHelper, $settingsHelper);
        $environment = $factory->buildEnvironment($request);

        self::assertSame(['prepared' => 'settings'], $environment->getSettings());
        self::assertSame('/ajax-uri', $environment->getAjaxUrl());
        self::assertSame(123, $environment->getId());

        $expectedRecord = [
            'uid' => 42,
            'header' => 'My Map',
        ];
        self::assertSame($expectedRecord, $environment->getContentRecord());
    }
}

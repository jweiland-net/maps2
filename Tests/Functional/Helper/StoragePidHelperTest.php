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
use JWeiland\Maps2\Helper\StoragePidHelper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\TypoScript\PageTsConfigFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test StoragePidHelper
 */
class StoragePidHelperTest extends FunctionalTestCase
{
    protected StoragePidHelper $subject;

    protected MessageHelper|MockObject $messageHelperMock;

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

        $this->messageHelperMock = $this->createMock(MessageHelper::class);

        $this->subject = new StoragePidHelper($this->messageHelperMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->messageHelperMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function getStoragePidWithoutPidAndNoRegistryConfigurationWillAddFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('Please check various places'),
                'Can not find a valid PID to store EXT:maps2 records',
            );

        $recordWithoutPid = [
            'uid' => 100,
            'title' => 'Market',
        ];
        $options = [];

        self::assertSame(
            0,
            $this->subject->getDefaultStoragePidForNewPoiCollection($recordWithoutPid, $options),
        );
    }

    #[Test]
    public function getStoragePidWithPidInForeignRecordWillReturnStoragePid(): void
    {
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create([], new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-200', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        $record = [
            'uid' => 100,
            'pid' => 200,
            'title' => 'Market',
        ];
        $options = [];

        self::assertSame(
            200,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }

    #[Test]
    public function getStoragePidWithHardCodedMaps2RegistryWillReturnStoragePid(): void
    {
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create([], new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-200', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        $record = [
            'uid' => 100,
            'pid' => 200,
            'title' => 'Market',
        ];
        $options = [
            'defaultStoragePid' => 428,
        ];

        self::assertSame(
            428,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }

    #[Test]
    public function getStoragePidWithHardCodedMaps2RegistryWillReturnUnifiedStoragePid(): void
    {
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create([], new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-200', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        $record = [
            'uid' => 100,
            'pid' => 200,
            'title' => 'Market',
        ];
        $options = [
            'defaultStoragePid' => '428',
        ];

        self::assertSame(
            428,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }

    #[Test]
    public function getStoragePidWithoutPidWillReturnPidFromExtensionManager(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['foreign_ext'] = [
            'maps2Storage' => 385,
        ];

        /** @var PackageManager|MockObject $packageManagerMock */
        $packageManagerMock = $this->createMock(PackageManager::class);
        $packageManagerMock
            ->expects(self::atLeastOnce())
            ->method('isPackageActive')
            ->with('foreign_ext')
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerMock);

        $recordWithoutPid = [
            'uid' => 100,
            'title' => 'Market',
        ];
        $options = [
            'defaultStoragePid' => [
                'extKey' => 'foreign_ext',
                'property' => 'maps2Storage',
            ],
        ];

        self::assertSame(
            385,
            $this->subject->getDefaultStoragePidForNewPoiCollection($recordWithoutPid, $options),
        );
    }

    #[Test]
    public function getStoragePidWithPidWillReturnPidFromExtensionManager(): void
    {
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create([], new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-200', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['foreign_ext'] = [
            'maps2Storage' => 197,
        ];

        /** @var PackageManager|MockObject $packageManagerMock */
        $packageManagerMock = $this->createMock(PackageManager::class);
        $packageManagerMock
            ->expects(self::atLeastOnce())
            ->method('isPackageActive')
            ->with('foreign_ext')
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerMock);

        $record = [
            'uid' => 100,
            'pid' => 200,
            'title' => 'Market',
        ];
        $options = [
            'defaultStoragePid' => [
                'extKey' => 'foreign_ext',
                'property' => 'maps2Storage',
            ],
        ];

        self::assertSame(
            197,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }

    #[Test]
    public function getStoragePidWithPidAndTypeWillReturnPidFromExtensionManager(): void
    {
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create([], new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-200', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['foreign_ext'] = [
            'maps2Storage' => 197,
        ];

        /** @var PackageManager|MockObject $packageManagerMock */
        $packageManagerMock = $this->createMock(PackageManager::class);
        $packageManagerMock
            ->expects(self::atLeastOnce())
            ->method('isPackageActive')
            ->with('foreign_ext')
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerMock);

        $record = [
            'uid' => 100,
            'pid' => 200,
            'title' => 'Market',
        ];
        $options = [
            'defaultStoragePid' => [
                'extKey' => 'foreign_ext',
                'property' => 'maps2Storage',
                'type' => 'ExtensionManager',
            ],
        ];

        self::assertSame(
            197,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }

    #[Test]
    public function getStoragePidWithPidWillReturnPidFromDefaultPageTsConfigPath(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => 'ext.maps2.defaultStoragePid = 582',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-5438', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market',
        ];
        $options = [];

        self::assertSame(
            582,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }

    #[Test]
    public function getStoragePidWithPidWillReturnPidFromConfiguredPageTsConfigPath(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => 'ext.maps2.defaultStoragePid = 582',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-5438', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market',
        ];
        $options = [
            'defaultStoragePid' => [
                'extKey' => 'foreign_ext',
                'property' => 'maps2Storage',
                'type' => 'pageTSconfig',
            ],
        ];

        self::assertSame(
            582,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }

    #[Test]
    public function getStoragePidWithPidWillOverridePidOfForeignExtWithPidOfDefaultPageTsConfig(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => 'ext.foreign_ext.maps2Storage = 491' . chr(10) . 'ext.maps2.defaultStoragePid = 927',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-5438', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market',
        ];
        $options = [
            'defaultStoragePid' => [
                'extKey' => 'foreign_ext',
                'property' => 'maps2Storage',
                'type' => 'pageTSconfig',
            ],
        ];

        self::assertSame(
            927,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }

    #[Test]
    public function getStoragePidWithPidWillOverridePidOfExtensionManagerWithPidOfPageTsConfig(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => 'ext.maps2.defaultStoragePid = 582',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-5438', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market',
        ];
        $options = [
            'defaultStoragePid' => 428,
        ];

        self::assertSame(
            582,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }

    #[Test]
    public function getStoragePidWithPidWillProcessVariousRegistryConfiguration(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => 'ext.foreign_ext.maps2Pid = 4297',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-5438', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        /** @var PackageManager|MockObject $packageManagerMock */
        $packageManagerMock = $this->createMock(PackageManager::class);
        $packageManagerMock
            ->expects(self::atLeastOnce())
            ->method('isPackageActive')
            ->willReturnMap([
                ['foreign_ext', true],
                ['events2', true],
                [self::any(), false],
            ]);
        ExtensionManagementUtility::setPackageManager($packageManagerMock);

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['foreign_ext'] = [
            'maps2Storage' => 0,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['events2'] = [
            'defaultLocationPid' => 0,
        ];

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market',
        ];
        $options = [
            'defaultStoragePid' => [
                0 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Storage',
                    'type' => 'extensionmanager',
                ],
                1 => [
                    'extKey' => 'events2',
                    'property' => 'defaultLocationPid',
                ],
                2 => [
                    'extKey' => 'news',
                    'property' => 'location',
                    'type' => 'pageTSconfig',
                ],
                3 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Pid',
                    'type' => 'pagetsconfig',
                ],
            ],
        ];

        self::assertSame(
            4297,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }

    #[Test]
    public function getStoragePidWithPidWillProcessTwoRegistryConfiguration(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => 'ext.foreign_ext.maps2Pid = 4297',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-5438', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['foreign_ext'] = [
            'invalidPidKey' => 3985,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['events2'] = [
            'defaultLocationPid' => 4867,
        ];

        /** @var PackageManager|MockObject $packageManagerMock */
        $packageManagerMock = $this->createMock(PackageManager::class);
        $packageManagerMock
            ->expects(self::atLeastOnce())
            ->method('isPackageActive')
            ->willReturnMap([
                ['foreign_ext', true],
                ['events2', true],
                [self::any(), false],
            ]);
        ExtensionManagementUtility::setPackageManager($packageManagerMock);

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market',
        ];
        $options = [
            'defaultStoragePid' => [
                0 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Storage',
                    'type' => 'extensionmanager',
                ],
                1 => [
                    'extKey' => 'events2',
                    'property' => 'defaultLocationPid',
                ],
                2 => [
                    'extKey' => 'news',
                    'property' => 'location',
                    'type' => 'pageTSconfig',
                ],
                3 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Pid',
                    'type' => 'pagetsconfig',
                ],
            ],
        ];

        self::assertSame(
            4867,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }

    #[Test]
    public function getStoragePidWithPidWillOverrideForeignPidWithPidOfDefaultPageTsConfig(): void
    {
        $rootLine = [
            [
                'uid' => 1,
                'TSconfig' => 'ext.foreign_ext.maps2Pid = 4297' . chr(10) . 'ext.maps2.defaultStoragePid = 5837',
            ],
        ];
        $subject = $this->get(PageTsConfigFactory::class);
        $pageTsConfig = $subject->create($rootLine, new NullSite());

        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['pageTsConfig-pid-to-hash-5438', 'Hash'],
                ['pageTsConfig-hash-to-object-Hash', $pageTsConfig],
            ]);

        /** @var CacheManager|MockObject $cacheManagerMock */
        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with(self::stringContains('runtime'))
            ->willReturn($variableFrontendMock);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        /** @var PackageManager|MockObject $packageManagerMock */
        $packageManagerMock = $this->createMock(PackageManager::class);
        $packageManagerMock
            ->expects(self::atLeastOnce())
            ->method('isPackageActive')
            ->willReturnMap([
                ['foreign_ext', true],
                ['events2', true],
                [self::any(), false],
            ]);
        ExtensionManagementUtility::setPackageManager($packageManagerMock);

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['foreign_ext'] = [
            'invalidPidKey' => 0,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['events2'] = [
            'defaultLocationPid' => 0,
        ];

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market',
        ];
        $options = [
            'defaultStoragePid' => [
                0 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Storage',
                    'type' => 'extensionmanager',
                ],
                1 => [
                    'extKey' => 'events2',
                    'property' => 'defaultLocationPid',
                ],
                2 => [
                    'extKey' => 'news',
                    'property' => 'location',
                    'type' => 'pageTSconfig',
                ],
                3 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Pid',
                    'type' => 'pagetsconfig',
                ],
            ],
        ];

        self::assertSame(
            5837,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options),
        );
    }
}

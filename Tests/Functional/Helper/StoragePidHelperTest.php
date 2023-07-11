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
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test StoragePidHelper
 */
class StoragePidHelperTest extends FunctionalTestCase
{
    protected StoragePidHelper $subject;

    /**
     * @var MessageHelper|MockObject
     */
    protected $messageHelperMock;

    protected array $testExtensionsToLoad = [
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
            $this->messageHelperMock
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getStoragePidWithoutPidAndNoRegistryConfigurationWillAddFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('Please check various places'),
                'Can not find a valid PID to store EXT:maps2 records'
            );

        $recordWithoutPid = [
            'uid' => 100,
            'title' => 'Market',
        ];
        $options = [];

        self::assertSame(
            0,
            $this->subject->getDefaultStoragePidForNewPoiCollection($recordWithoutPid, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidInForeignRecordWillReturnStoragePid(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [],
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithHardCodedMaps2RegistryWillReturnStoragePid(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [],
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithHardCodedMaps2RegistryWillReturnUnifiedStoragePid(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [],
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($recordWithoutPid, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillReturnPidFromExtensionManager(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [],
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidAndTypeWillReturnPidFromExtensionManager(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [],
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillReturnPidFromDefaultPageTsConfigPath(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [
                    'ext.' => [
                        'maps2.' => [
                            'defaultStoragePid' => 582,
                        ],
                    ],
                ],
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillReturnPidFromConfiguredPageTsConfigPath(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [
                    'ext.' => [
                        'foreign_ext.' => [
                            'maps2Storage' => 582,
                        ],
                    ],
                ],
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillOverridePidOfForeignExtWithPidOfDefaultPageTsConfig(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [
                    'ext.' => [
                        'foreign_ext.' => [
                            'maps2Storage' => 491,
                        ],
                        'maps2.' => [
                            'defaultStoragePid' => 927,
                        ],
                    ],
                ],
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillOverridePidOfExtensionManagerWithPidOfPageTsConfig(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [
                    'ext.' => [
                        'maps2.' => [
                            'defaultStoragePid' => 582,
                        ],
                    ],
                ],
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillProcessVariousRegistryConfiguration(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [
                    'ext.' => [
                        'foreign_ext.' => [
                            'maps2Pid' => 4297,
                        ],
                    ],
                ],
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
                'foreign_ext', true,
                'events2', true,
                self::any(), false,
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillProcessTwoRegistryConfiguration(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [
                    'ext.' => [
                        'foreign_ext.' => [
                            'maps2Pid' => 4297,
                        ],
                    ],
                ],
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
                'foreign_ext', true,
                'events2', true,
                self::any(), false,
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillOverrideForeignPidWithPidOfDefaultPageTsConfig(): void
    {
        /** @var VariableFrontend|MockObject $variableFrontendMock */
        $variableFrontendMock = $this->createMock(VariableFrontend::class);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('has')
            ->with('pagesTsConfigIdToHash200')
            ->willReturn(true);
        $variableFrontendMock
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                'pagesTsConfigIdToHash200', 'Blub',
                'pagesTsConfigHashToContentBlub', [
                    'ext.' => [
                        'foreign_ext.' => [
                            'maps2Pid' => 4297,
                        ],
                        'maps2.' => [
                            'defaultStoragePid' => 5837,
                        ],
                    ],
                ],
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
                'foreign_ext', true,
                'events2', true,
                self::any(), false,
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
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }
}

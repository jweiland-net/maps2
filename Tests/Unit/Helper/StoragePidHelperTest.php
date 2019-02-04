<?php
namespace JWeiland\Maps2\Tests\Unit\Helper;

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

use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Helper\StoragePidHelper;
use JWeiland\Maps2\Tests\Unit\AbstractUnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case
 */
class StoragePidHelperTest extends AbstractUnitTestCase
{
    /**
     * @var StoragePidHelper
     */
    protected $subject;

    /**
     * @var MessageHelper|ObjectProphecy
     */
    protected $messageHelperProphecy;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->messageHelperProphecy = $this->prophesize(MessageHelper::class);
        $this->subject = new StoragePidHelper($this->messageHelperProphecy->reveal());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset(
            $this->subject,
            $this->messageHelperProphecy
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getStoragePidWithoutPidAndNoRegistryConfigurationWillAddFlashMessage()
    {
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('Please check various places'),
                'Can not find a valid PID to store EXT:maps2 records'
            )
            ->shouldBeCalled();

        $recordWithoutPid = [
            'uid' => 100,
            'title' => 'Market'
        ];
        $options = [];

        $this->assertSame(
            0,
            $this->subject->getDefaultStoragePidForNewPoiCollection($recordWithoutPid, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidInForeignRecordWillReturnStoragePid()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash200')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash200')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 200,
            'title' => 'Market'
        ];
        $options = [];

        $this->assertSame(
            200,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithHardCodedMaps2RegistryWillReturnStoragePid()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash200')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash200')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 200,
            'title' => 'Market'
        ];
        $options = [
            'defaultStoragePid' => 428
        ];

        $this->assertSame(
            428,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithHardCodedMaps2RegistryWillReturnUnifiedStoragePid()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash200')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash200')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 200,
            'title' => 'Market'
        ];
        $options = [
            'defaultStoragePid' => '428'
        ];

        $this->assertSame(
            428,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithoutPidWillReturnPidFromExtensionManager()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['foreign_ext'] = serialize([
            'maps2Storage' => 385
        ]);

        /** @var PackageManager|ObjectProphecy $packageManagerProphecy */
        $packageManagerProphecy = $this->prophesize(PackageManager::class);
        $packageManagerProphecy
            ->isPackageActive('foreign_ext')
            ->shouldBeCalled()
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerProphecy->reveal());

        $recordWithoutPid = [
            'uid' => 100,
            'title' => 'Market'
        ];
        $options = [
            'defaultStoragePid' => [
                'extKey' => 'foreign_ext',
                'property' => 'maps2Storage'
            ]
        ];

        $this->assertSame(
            385,
            $this->subject->getDefaultStoragePidForNewPoiCollection($recordWithoutPid, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillReturnPidFromExtensionManager()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash200')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash200')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['foreign_ext'] = serialize([
            'maps2Storage' => 197
        ]);

        /** @var PackageManager|ObjectProphecy $packageManagerProphecy */
        $packageManagerProphecy = $this->prophesize(PackageManager::class);
        $packageManagerProphecy
            ->isPackageActive('foreign_ext')
            ->shouldBeCalled()
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 200,
            'title' => 'Market'
        ];
        $options = [
            'defaultStoragePid' => [
                'extKey' => 'foreign_ext',
                'property' => 'maps2Storage'
            ]
        ];

        $this->assertSame(
            197,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidAndTypeWillReturnPidFromExtensionManager()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash200')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash200')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['foreign_ext'] = serialize([
            'maps2Storage' => 197
        ]);

        /** @var PackageManager|ObjectProphecy $packageManagerProphecy */
        $packageManagerProphecy = $this->prophesize(PackageManager::class);
        $packageManagerProphecy
            ->isPackageActive('foreign_ext')
            ->shouldBeCalled()
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 200,
            'title' => 'Market'
        ];
        $options = [
            'defaultStoragePid' => [
                'extKey' => 'foreign_ext',
                'property' => 'maps2Storage',
                'type' => 'ExtensionManager'
            ]
        ];

        $this->assertSame(
            197,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillReturnPidFromDefaultPageTsConfigPath()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([
                'ext.' => [
                    'maps2.' => [
                        'defaultStoragePid' => 582
                    ]
                ]
            ]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market'
        ];
        $options = [];

        $this->assertSame(
            582,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillReturnPidFromConfiguredPageTsConfigPath()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([
                'ext.' => [
                    'foreign_ext.' => [
                        'maps2Storage' => 582
                    ]
                ]
            ]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market'
        ];
        $options = [
            'defaultStoragePid' => [
                'extKey' => 'foreign_ext',
                'property' => 'maps2Storage',
                'type' => 'pageTSconfig'
            ]
        ];

        $this->assertSame(
            582,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillOverridePidOfForeignExtWithPidOfDefaultPageTsConfig()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([
                'ext.' => [
                    'foreign_ext.' => [
                        'maps2Storage' => 491
                    ],
                    'maps2.' => [
                        'defaultStoragePid' => 927
                    ]
                ]
            ]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market'
        ];
        $options = [
            'defaultStoragePid' => [
                'extKey' => 'foreign_ext',
                'property' => 'maps2Storage',
                'type' => 'pageTSconfig'
            ]
        ];

        $this->assertSame(
            927,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillOverridePidOfExtensionManagerWithPidOfPageTsConfig()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([
                'ext.' => [
                    'maps2.' => [
                        'defaultStoragePid' => 582
                    ]
                ]
            ]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market'
        ];
        $options = [
            'defaultStoragePid' => 428
        ];

        $this->assertSame(
            582,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillProcessVariousRegistryConfiguration()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([
                'ext.' => [
                    'foreign_ext.' => [
                        'maps2Pid' => 4297
                    ]
                ]
            ]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        /** @var PackageManager|ObjectProphecy $packageManagerProphecy */
        $packageManagerProphecy = $this->prophesize(PackageManager::class);
        $packageManagerProphecy
            ->isPackageActive('foreign_ext')
            ->shouldBeCalled()
            ->willReturn(true);
        $packageManagerProphecy
            ->isPackageActive('events2')
            ->shouldBeCalled()
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market'
        ];
        $options = [
            'defaultStoragePid' => [
                0 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Storage',
                    'type' => 'extensionmanager'
                ],
                1 => [
                    'extKey' => 'events2',
                    'property' => 'defaultLocationPid',
                ],
                2 => [
                    'extKey' => 'news',
                    'property' => 'location',
                    'type' => 'pageTSconfig'
                ],
                3 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Pid',
                    'type' => 'pagetsconfig'
                ],
            ]
        ];

        $this->assertSame(
            4297,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillProcessTwoRegistryConfiguration()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([
                'ext.' => [
                    'foreign_ext.' => [
                        'maps2Pid' => 4297
                    ]
                ]
            ]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['events2'] = serialize([
            'defaultLocationPid' => 4867
        ]);

        /** @var PackageManager|ObjectProphecy $packageManagerProphecy */
        $packageManagerProphecy = $this->prophesize(PackageManager::class);
        $packageManagerProphecy
            ->isPackageActive('foreign_ext')
            ->shouldBeCalled()
            ->willReturn(true);
        $packageManagerProphecy
            ->isPackageActive('events2')
            ->shouldBeCalled()
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market'
        ];
        $options = [
            'defaultStoragePid' => [
                0 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Storage',
                    'type' => 'extensionmanager'
                ],
                1 => [
                    'extKey' => 'events2',
                    'property' => 'defaultLocationPid',
                ],
                2 => [
                    'extKey' => 'news',
                    'property' => 'location',
                    'type' => 'pageTSconfig'
                ],
                3 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Pid',
                    'type' => 'pagetsconfig'
                ],
            ]
        ];

        $this->assertSame(
            4867,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }

    /**
     * @test
     */
    public function getStoragePidWithPidWillOverrideForeignPidWithPidOfDefaultPageTsConfig()
    {
        /** @var VariableFrontend|ObjectProphecy $variableFrontend */
        $variableFrontend = $this->prophesize(VariableFrontend::class);
        $variableFrontend
            ->has('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn(true);
        $variableFrontend
            ->get('pagesTsConfigIdToHash5438')
            ->shouldBeCalled()
            ->willReturn('Blub');
        $variableFrontend
            ->get('pagesTsConfigHashToContentBlub')
            ->shouldBeCalled()
            ->willReturn([
                'ext.' => [
                    'foreign_ext.' => [
                        'maps2Pid' => 4297
                    ],
                    'maps2.' => [
                        'defaultStoragePid' => 5837
                    ],
                ]
            ]);

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('cache_runtime')
            ->shouldBeCalled()
            ->willReturn($variableFrontend->reveal());
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        /** @var PackageManager|ObjectProphecy $packageManagerProphecy */
        $packageManagerProphecy = $this->prophesize(PackageManager::class);
        $packageManagerProphecy
            ->isPackageActive('foreign_ext')
            ->shouldBeCalled()
            ->willReturn(true);
        $packageManagerProphecy
            ->isPackageActive('events2')
            ->shouldBeCalled()
            ->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManagerProphecy->reveal());

        $record = [
            'uid' => 100,
            'pid' => 5438,
            'title' => 'Market'
        ];
        $options = [
            'defaultStoragePid' => [
                0 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Storage',
                    'type' => 'extensionmanager'
                ],
                1 => [
                    'extKey' => 'events2',
                    'property' => 'defaultLocationPid',
                ],
                2 => [
                    'extKey' => 'news',
                    'property' => 'location',
                    'type' => 'pageTSconfig'
                ],
                3 => [
                    'extKey' => 'foreign_ext',
                    'property' => 'maps2Pid',
                    'type' => 'pagetsconfig'
                ],
            ]
        ];

        $this->assertSame(
            5837,
            $this->subject->getDefaultStoragePidForNewPoiCollection($record, $options)
        );
    }
}

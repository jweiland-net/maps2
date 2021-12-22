<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Hook;

use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Hook\CreateMaps2RecordHook;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use JWeiland\Maps2\Tca\Maps2Registry;
use JWeiland\Maps2\Tests\Functional\Fixtures\IsRecordAllowedToCreatePoiCollectionSignal;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Functional test for CreateMaps2RecordHook
 */
class CreateMaps2RecordHookTest extends FunctionalTestCase
{
    use ProphecyTrait;

    /**
     * @var array
     */
    protected $maps2RegistryConfiguration = [];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBackendUserFromFixture(1);

        $this->importDataSet(__DIR__ . '/../Fixtures/fe_groups.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/fe_users.xml');

        $this->maps2RegistryConfiguration = [
            'fe_users' => [
                'lastlogin' => [
                    'addressColumns' => ['address', 'zip', 'city'],
                    'countryColumn' => 'country',
                    'columnMatch' => [
                        'pid' => '12'
                    ],
                    'defaultStoragePid' => '21',
                    'synchronizeColumns' => [
                        [
                            'foreignColumnName' => 'username',
                            'poiCollectionColumnName' => 'title'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     */
    public function processDatamapClearsInfoWindowContentCacheIfTableIsPoiCollection(): void
    {
        /** @var FrontendInterface|ObjectProphecy $cacheProphecy */
        $cacheProphecy = $this->prophesize(VariableFrontend::class);
        $cacheProphecy
            ->flushByTag('infoWindowUid123')
            ->shouldBeCalled();
        $cacheProphecy
            ->flushByTag('infoWindowUid234')
            ->shouldBeCalled();

        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheManagerProphecy
            ->getCache('maps2_cachedhtml')
            ->shouldBeCalled()
            ->willReturn($cacheProphecy->reveal());
        $cacheManagerProphecy->getCache(Argument::any())->shouldBeCalled();
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerProphecy->reveal());

        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'tx_maps2_domain_model_poicollection' => [
                '123' => [
                    'uid' => '123',
                    'pid' => '12',
                    'title' => 'Test',
                    'l10n_parent' => '234',
                ]
            ]
        ];

        $mapService = new MapService();
        $mapService->setColumnRegistry($this->maps2RegistryConfiguration);

        $hook = new CreateMaps2RecordHook(
            null,
            null,
            null,
            $mapService
        );
        $hook->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * @test
     */
    public function processDatamapWillGetForeignLocationRecord(): void
    {
        /** @var Dispatcher|ObjectProphecy $dispatcherProphecy */
        $dispatcherProphecy = $this->prophesize(Dispatcher::class);
        $dispatcherProphecy
            ->dispatch(
                CreateMaps2RecordHook::class,
                'preIsRecordAllowedToCreatePoiCollection',
                Argument::allOf(
                    Argument::withEntry(
                        0,
                        Argument::allOf(
                            Argument::withEntry('uid', 1),
                            Argument::withEntry('username', 'Stefan')
                        )
                    ),
                    Argument::withEntry(4, true)
                )
            )
            ->shouldBeCalled();
        $dispatcherProphecy
            ->dispatch(Argument::cetera())
            ->shouldBeCalled();

        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'fe_users' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'username' => 'Stefan',
                    'lastlogin' => '0',
                ]
            ]
        ];

        $mapService = new MapService();

        /** @var Maps2Registry|ObjectProphecy $maps2Registry */
        $maps2Registry = $this->prophesize(Maps2Registry::class);
        $maps2Registry
            ->getColumnRegistry()
            ->shouldBeCalled()
            ->willReturn($this->maps2RegistryConfiguration);

        $hook = new CreateMaps2RecordHook(
            null,
            $this->prophesize(MessageHelper::class)->reveal(),
            $dispatcherProphecy->reveal(),
            $mapService,
            $maps2Registry->reveal()
        );
        $hook->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * @test
     */
    public function processDatamapInvalidForeignRecordBecausePidIsNotEqual(): void
    {
        $maps2RegistryConfiguration = $this->maps2RegistryConfiguration;
        $maps2RegistryConfiguration['fe_users']['lastlogin']['columnMatch']['pid'] = 432;

        /** @var Dispatcher|ObjectProphecy $dispatcherProphecy */
        $dispatcherProphecy = $this->prophesize(Dispatcher::class);
        $dispatcherProphecy
            ->dispatch(
                CreateMaps2RecordHook::class,
                'preIsRecordAllowedToCreatePoiCollection',
                Argument::withEntry(4, false)
            )
            ->shouldBeCalled();
        $dispatcherProphecy
            ->dispatch(Argument::cetera())
            ->shouldBeCalled();

        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'fe_users' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'username' => 'Stefan',
                    'lastlogin' => '0',
                ]
            ]
        ];

        $mapService = new MapService();

        /** @var Maps2Registry|ObjectProphecy $maps2Registry */
        $maps2Registry = $this->prophesize(Maps2Registry::class);
        $maps2Registry
            ->getColumnRegistry()
            ->shouldBeCalled()
            ->willReturn($maps2RegistryConfiguration);

        $hook = new CreateMaps2RecordHook(
            null,
            $this->prophesize(MessageHelper::class)->reveal(),
            $dispatcherProphecy->reveal(),
            $mapService,
            $maps2Registry->reveal()
        );
        $hook->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * Provides various expression configuration
     *
     * @return array
     */
    public function dataProcessorForExpressions(): array
    {
        return [
            'Record invalid if pid is 432' => [['pid' => ['expr' => 'eq', 'value' => '432']], false],
            'Record invalid if pid is greater than 12' => [['pid' => ['expr' => 'gt', 'value' => '12']], false],
            'Record invalid if pid is greater than or equals 13' => [['pid' => ['expr' => 'gte', 'value' => '13']], false],
            'Record invalid if pid is less than 12' => [['pid' => ['expr' => 'lt', 'value' => '12']], false],
            'Record invalid if pid is less than or equals 11' => [['pid' => ['expr' => 'lte', 'value' => '11']], false],
            'Record invalid if pid is in list of 11,13,14' => [['pid' => ['expr' => 'in', 'value' => '11,13,14']], false],
            'Record valid if pid is 12' => [['pid' => ['expr' => 'eq', 'value' => '12']], true],
            'Record valid if pid is greater than 8' => [['pid' => ['expr' => 'gt', 'value' => '8']], true],
            'Record valid if pid is greater than or equals 12' => [['pid' => ['expr' => 'gte', 'value' => '12']], true],
            'Record valid if pid is less than 15' => [['pid' => ['expr' => 'lt', 'value' => '15']], true],
            'Record valid if pid is less than or equals 12' => [['pid' => ['expr' => 'lte', 'value' => '12']], true],
            'Record valid if pid is in list of 11,12,13,14' => [['pid' => ['expr' => 'in', 'value' => '11,12,13,14']], true],
        ];
    }

    /**
     * @test
     *
     * @param array $columnMatch
     * @param bool $isValid
     * @dataProvider dataProcessorForExpressions
     */
    public function processDatamapInvalidForeignRecordBecauseExpressionsAreNotEqual(
        array $columnMatch,
        bool $isValid
    ): void {
        $maps2RegistryConfiguration = $this->maps2RegistryConfiguration;
        $maps2RegistryConfiguration['fe_users']['lastlogin']['columnMatch'] = $columnMatch;

        /** @var Dispatcher|ObjectProphecy $dispatcherProphecy */
        $dispatcherProphecy = $this->prophesize(Dispatcher::class);
        $dispatcherProphecy
            ->dispatch(
                CreateMaps2RecordHook::class,
                'preIsRecordAllowedToCreatePoiCollection',
                Argument::withEntry(4, $isValid)
            )
            ->shouldBeCalled();
        $dispatcherProphecy
            ->dispatch(Argument::cetera())
            ->shouldBeCalled();

        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'fe_users' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'username' => 'Stefan',
                    'lastlogin' => '0',
                ]
            ]
        ];

        $mapService = new MapService();

        /** @var Maps2Registry|ObjectProphecy $maps2Registry */
        $maps2Registry = $this->prophesize(Maps2Registry::class);
        $maps2Registry
            ->getColumnRegistry()
            ->shouldBeCalled()
            ->willReturn($maps2RegistryConfiguration);

        $hook = new CreateMaps2RecordHook(
            null,
            $this->prophesize(MessageHelper::class)->reveal(),
            $dispatcherProphecy->reveal(),
            $mapService,
            $maps2Registry->reveal()
        );
        $hook->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * @test
     */
    public function processDatamapCreatesNewPoiCollection(): void
    {
        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'fe_users' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'username' => 'Stefan',
                    'lastlogin' => '0',
                ]
            ]
        ];

        /** @var Position|ObjectProphecy $positionProphecy */
        $positionProphecy = $this->prophesize(Position::class);
        $positionProphecy
            ->getLatitude()
            ->shouldBeCalled()
            ->willReturn(12.34);
        $positionProphecy
            ->getLongitude()
            ->shouldBeCalled()
            ->willReturn(56.78);
        $positionProphecy
            ->getFormattedAddress()
            ->shouldBeCalled()
            ->willReturn('Echterdinger Straße 57, 70794 Filderstadt, Deutschland');
        /** @var GeoCodeService|ObjectProphecy $geoCodeServiceProphecy */
        $geoCodeServiceProphecy = $this->prophesize(GeoCodeService::class);
        $geoCodeServiceProphecy
            ->getFirstFoundPositionByAddress('Echterdinger Straße 57 70794 Filderstadt Deutschland')
            ->shouldBeCalled()
            ->willReturn($positionProphecy->reveal());

        /** @var Dispatcher|ObjectProphecy $dispatcherProphecy */
        $dispatcherProphecy = $this->prophesize(Dispatcher::class);
        $dispatcherProphecy
            ->dispatch(
                CreateMaps2RecordHook::class,
                'postUpdatePoiCollection',
                Argument::allOf(
                    Argument::withEntry(1, 1),
                    Argument::withEntry(3, Argument::withEntry('lastlogin', 1))
                )
            )
            ->shouldBeCalled();
        $dispatcherProphecy
            ->dispatch(Argument::cetera())
            ->shouldBeCalled();

        $mapService = new MapService();

        /** @var Maps2Registry|ObjectProphecy $maps2Registry */
        $maps2Registry = $this->prophesize(Maps2Registry::class);
        $maps2Registry
            ->getColumnRegistry()
            ->shouldBeCalled()
            ->willReturn($this->maps2RegistryConfiguration);

        $hook = new CreateMaps2RecordHook(
            $geoCodeServiceProphecy->reveal(),
            $this->prophesize(MessageHelper::class)->reveal(),
            $dispatcherProphecy->reveal(),
            $mapService,
            $maps2Registry->reveal()
        );
        $hook->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * @test
     */
    public function processDatamapDoesNotCreatesPoiCollectionBecauseOfHook(): void
    {
        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'fe_users' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'username' => 'Stefan',
                    'lastlogin' => '0',
                ]
            ]
        ];

        /** @var GeoCodeService|ObjectProphecy $geoCodeServiceProphecy */
        $geoCodeServiceProphecy = $this->prophesize(GeoCodeService::class);
        $geoCodeServiceProphecy
            ->getFirstFoundPositionByAddress('Echterdinger Straße 57 70794 Filderstadt Deutschland')
            ->shouldNotBeCalled();

        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher
            ->connect(
                CreateMaps2RecordHook::class,
                'preIsRecordAllowedToCreatePoiCollection',
                IsRecordAllowedToCreatePoiCollectionSignal::class,
                'invalidPoiCollection'
            );

        $mapService = new MapService();

        /** @var Maps2Registry|ObjectProphecy $maps2Registry */
        $maps2Registry = $this->prophesize(Maps2Registry::class);
        $maps2Registry
            ->getColumnRegistry()
            ->shouldBeCalled()
            ->willReturn($this->maps2RegistryConfiguration);

        $hook = new CreateMaps2RecordHook(
            $geoCodeServiceProphecy->reveal(),
            $this->prophesize(MessageHelper::class)->reveal(),
            null,
            $mapService,
            $maps2Registry->reveal()
        );
        $hook->processDatamap_afterAllOperations($dataHandler);
    }
}

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
use JWeiland\Maps2\Event\AllowCreationOfPoiCollectionEvent;
use JWeiland\Maps2\Helper\AddressHelper;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Helper\StoragePidHelper;
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
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Functional test for CreateMaps2RecordHook
 */
class CreateMaps2RecordHookTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected CreateMaps2RecordHook $subject;

    /**
     * @var GeoCodeService|ObjectProphecy
     */
    protected $geoCodeServiceProphecy;

    /**
     * @var MessageHelper|ObjectProphecy
     */
    protected $messageHelperProphecy;

    /**
     * @var MapService|ObjectProphecy
     */
    protected $mapServiceProphecy;

    /**
     * @var EventDispatcher|ObjectProphecy
     */
    protected $eventDispatcherProphecy;

    protected Maps2Registry $maps2Registry;

    protected array $maps2RegistryOptions = [];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/events2',
        'typo3conf/ext/maps2'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBackendUserFromFixture(1);

        $this->importDataSet(__DIR__ . '/../Fixtures/tx_events2_domain_model_location.xml');

        $this->maps2RegistryOptions = [
            'addressColumns' => ['street', 'zip', 'city'],
            'countryColumn' => 'country',
            'columnMatch' => [
                'pid' => '12'
            ],
            'defaultStoragePid' => '21',
            'synchronizeColumns' => [
                [
                    'foreignColumnName' => 'location',
                    'poiCollectionColumnName' => 'title'
                ]
            ]
        ];

        $this->geoCodeServiceProphecy = $this->prophesize(GeoCodeService::class);
        $this->messageHelperProphecy = $this->prophesize(MessageHelper::class);
        $this->mapServiceProphecy = $this->prophesize(MapService::class);
        $this->eventDispatcherProphecy = $this->prophesize(EventDispatcher::class);

        $this->maps2Registry = new Maps2Registry();
        $this->maps2Registry->add(
            'frontend',
            'fe_users',
            $this->maps2RegistryOptions
        );

        $this->subject = new CreateMaps2RecordHook(
            $this->geoCodeServiceProphecy->reveal(),
            new AddressHelper($this->messageHelperProphecy->reveal()),
            $this->messageHelperProphecy->reveal(),
            new StoragePidHelper($this->messageHelperProphecy->reveal()),
            $this->mapServiceProphecy->reveal(),
            $this->maps2Registry,
            $this->eventDispatcherProphecy->reveal()
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->geoCodeServiceProphecy,
            $this->messageHelperProphecy,
            $this->mapServiceProphecy,
            $this->eventDispatcherProphecy,
            $this->maps2Registry
        );

        parent::tearDown();
    }

    /**
     * @tester
     */
    public function processDatamapWithInvalidTableNameWillNotStartRecordCreation(): void
    {
        $this->eventDispatcherProphecy
            ->dispatch(Argument::any())
            ->shouldNotBeCalled();

        $this->subject->processDatamap_afterAllOperations(
            new DataHandler()
        );
    }

    /**
     * @tester
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

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * @test
     */
    public function processDatamapWillGetForeignLocationRecord(): void
    {
        $this->eventDispatcherProphecy
            ->dispatch(new AllowCreationOfPoiCollectionEvent(
                [],
                'tx_events2_domain_model_location',
                'tx_maps2_uid',
                $this->maps2RegistryOptions,
                false
            ))
            ->shouldBeCalled();

        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'tx_events2_domain_model_location' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'location' => 'Stefan',
                    'tx_maps2_uid' => 1
                ]
            ]
        ];

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * @tester
     */
    public function processDatamapInvalidForeignRecordBecausePidIsNotEqual(): void
    {
        $maps2RegistryOptions = $this->maps2RegistryOptions;
        $maps2RegistryOptions['fe_users']['lastlogin']['columnMatch']['pid'] = 432;
        $this->maps2Registry->add(
            'frontend',
            'fe_users',
            $maps2RegistryOptions,
            'tx_maps2_uid',
            true
        );

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
            'tx_events2_domain_model_location' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'location' => 'Stefan',
                    'tx_maps2_uid' => 1
                ]
            ]
        ];

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * Provides various expression configuration
     *
     * @return array<string, array<array<string, array<string, string>>|bool>>
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
     * @tester
     *
     * @dataProvider dataProcessorForExpressions
     * @param array<string, mixed[]> $columnMatch
     */
    public function processDatamapInvalidForeignRecordBecauseExpressionsAreNotEqual(
        array $columnMatch,
        bool $isValid
    ): void {
        $maps2RegistryOptions = $this->maps2RegistryOptions;
        $maps2RegistryOptions['fe_users']['lastlogin']['columnMatch'] = $columnMatch;
        $this->maps2Registry->add(
            'frontend',
            'fe_users',
            $maps2RegistryOptions,
            'tx_maps2_uid',
            true
        );

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
            'tx_events2_domain_model_location' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'location' => 'Stefan',
                    'tx_maps2_uid' => 1
                ]
            ]
        ];

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * @tester
     */
    public function processDatamapCreatesNewPoiCollection(): void
    {
        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'tx_events2_domain_model_location' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'location' => 'Stefan',
                    'tx_maps2_uid' => 1
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

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * @tester
     */
    public function processDatamapDoesNotCreatesPoiCollectionBecauseOfHook(): void
    {
        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'tx_events2_domain_model_location' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'location' => 'Stefan',
                    'tx_maps2_uid' => 1
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

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }
}

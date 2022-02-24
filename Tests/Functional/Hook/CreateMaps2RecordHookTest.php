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
use JWeiland\Maps2\Event\PostProcessPoiCollectionRecordEvent;
use JWeiland\Maps2\Helper\AddressHelper;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Helper\StoragePidHelper;
use JWeiland\Maps2\Hook\CreateMaps2RecordHook;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use JWeiland\Maps2\Tca\Maps2Registry;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

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
     * @var Maps2Registry|ObjectProphecy
     */
    protected $maps2RegistryProphecy;

    /**
     * @var ListenerProvider|ObjectProphecy
     */
    protected $listenerProviderProphecy;

    protected array $columnRegistry = [
        'tx_events2_domain_model_location' => [
            'tx_maps2_uid' => [
                'addressColumns' => ['street', 'house_number', 'zip', 'city'],
                'countryColumn' => 'country',
                'defaultStoragePid' => [
                    'extKey' => 'events2',
                    'property' => 'poiCollectionPid'
                ],
                'synchronizeColumns' => [
                    [
                        'foreignColumnName' => 'location',
                        'poiCollectionColumnName' => 'title'
                    ],
                    [
                        'foreignColumnName' => 'hidden',
                        'poiCollectionColumnName' => 'hidden'
                    ]
                ]
            ]
        ]
    ];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/events2',
        'typo3conf/ext/maps2'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBackendUserFromFixture(1);

        $this->importDataSet(__DIR__ . '/../Fixtures/static_countries.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_events2_domain_model_location.xml');

        $this->geoCodeServiceProphecy = $this->prophesize(GeoCodeService::class);
        $this->messageHelperProphecy = $this->prophesize(MessageHelper::class);
        $this->maps2RegistryProphecy = $this->prophesize(Maps2Registry::class);

        $this->maps2RegistryProphecy
            ->getColumnRegistry()
            ->willReturn($this->columnRegistry);

        $this->listenerProviderProphecy = $this->prophesize(ListenerProvider::class);
        $this->listenerProviderProphecy
            ->getListenersForEvent(Argument::any())
            ->willReturn([]);

        $this->subject = new CreateMaps2RecordHook(
            $this->geoCodeServiceProphecy->reveal(),
            new AddressHelper($this->messageHelperProphecy->reveal()),
            $this->messageHelperProphecy->reveal(),
            new StoragePidHelper($this->messageHelperProphecy->reveal()),
            new MapService(
                $this->prophesize(ConfigurationManager::class)->reveal(),
                $this->messageHelperProphecy->reveal(),
                $this->maps2RegistryProphecy->reveal()
            ),
            $this->maps2RegistryProphecy->reveal(),
            new EventDispatcher($this->listenerProviderProphecy->reveal())
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->geoCodeServiceProphecy,
            $this->messageHelperProphecy,
            $this->mapService,
            $this->eventDispatcher,
            $this->maps2RegistryProphecy,
            $this->listenerProviderProphecy
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function processDatamapWithInvalidTableNameWillNotStartRecordCreation(): void
    {
        $this->listenerProviderProphecy
            ->getListenersForEvent(Argument::any())
            ->shouldNotBeCalled();

        $this->subject->processDatamap_afterAllOperations(
            new DataHandler()
        );
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

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * @test
     */
    public function processDatamapWillBreakIfPoiCollectionIsNotAllowedToBeCreated(): void
    {
        $event = new AllowCreationOfPoiCollectionEvent(
            $this->getDatabaseConnection()->selectSingleRow('*', 'tx_events2_domain_model_location', 'uid = 1'),
            'tx_events2_domain_model_location',
            'tx_maps2_uid',
            $this->columnRegistry['tx_events2_domain_model_location']['tx_maps2_uid'],
            true
        );

        $listenerWithInvalidFlag = static function (AllowCreationOfPoiCollectionEvent $event): void {
            $event->setIsValid(false);
        };

        // First EventListener will be called with foreignLocationRecord and returns false to break further processing
        $this->listenerProviderProphecy
            ->getListenersForEvent($event)
            ->shouldBeCalled()
            ->willReturn([$listenerWithInvalidFlag]);

        // Second EventListener should not be called. It's true, if break is working
        $this->listenerProviderProphecy
            ->getListenersForEvent(Argument::type(PostProcessPoiCollectionRecordEvent::class))
            ->shouldNotBeCalled();

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
     * @test
     */
    public function processDatamapInvalidForeignRecordBecausePidIsNotEqual(): void
    {
        $columnRegistry = $this->columnRegistry;
        $columnRegistry['tx_events2_domain_model_location']['tx_maps2_uid']['columnMatch']['pid'] = 432;

        $this->maps2RegistryProphecy
            ->getColumnRegistry()
            ->shouldBeCalled()
            ->willReturn($columnRegistry);

        // If EventListener was not called, the columnMatch was invalid.
        $this->listenerProviderProphecy
            ->getListenersForEvent(Argument::type(PostProcessPoiCollectionRecordEvent::class))
            ->shouldNotBeCalled();

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
     * @test
     *
     * @dataProvider dataProcessorForExpressions
     */
    public function processDatamapInvalidForeignRecordBecauseExpressionsAreNotEqual(
        array $columnMatch,
        bool $isValid
    ): void {
        $columnRegistry = $this->columnRegistry;
        $columnRegistry['tx_events2_domain_model_location']['tx_maps2_uid']['columnMatch'] = $columnMatch;

        $this->maps2RegistryProphecy
            ->getColumnRegistry()
            ->shouldBeCalled()
            ->willReturn($columnRegistry);

        if ($isValid) {
            $this->listenerProviderProphecy
                ->getListenersForEvent(Argument::type(PostProcessPoiCollectionRecordEvent::class))
                ->shouldBeCalled()
                ->willReturn([]);
        } else {
            $this->listenerProviderProphecy
                ->getListenersForEvent(Argument::type(PostProcessPoiCollectionRecordEvent::class))
                ->shouldNotBeCalled();
        }

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
     * @test
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
            ->willReturn('Echterdinger Straße 57, 70794 Filderstadt, Germany');

        $this->geoCodeServiceProphecy
            ->getFirstFoundPositionByAddress('Echterdinger Straße 57 70794 Filderstadt Germany')
            ->shouldBeCalled()
            ->willReturn($positionProphecy->reveal());

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }
}

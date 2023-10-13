<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Hook;

use JWeiland\Maps2\Configuration\ExtConf;
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
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional test for CreateMaps2RecordHook
 */
class CreateMaps2RecordHookTest extends FunctionalTestCase
{
    protected CreateMaps2RecordHook $subject;

    /**
     * @var GeoCodeService|MockObject
     */
    protected $geoCodeServiceMock;

    /**
     * @var MessageHelper|MockObject
     */
    protected $messageHelperMock;

    /**
     * @var Maps2Registry|MockObject
     */
    protected $maps2RegistryMock;

    /**
     * @var ListenerProvider|MockObject
     */
    protected $listenerProviderMock;

    protected array $columnRegistry = [
        'tx_events2_domain_model_location' => [
            'tx_maps2_uid' => [
                'addressColumns' => ['street', 'house_number', 'zip', 'city'],
                'countryColumn' => 'country',
                'defaultStoragePid' => [
                    'extKey' => 'events2',
                    'property' => 'poiCollectionPid',
                ],
                'synchronizeColumns' => [
                    [
                        'foreignColumnName' => 'location',
                        'poiCollectionColumnName' => 'title',
                    ],
                    [
                        'foreignColumnName' => 'hidden',
                        'poiCollectionColumnName' => 'hidden',
                    ],
                ],
            ],
        ],
    ];

    protected array $testExtensionsToLoad = [
        'sjbr/static-info-tables',
        'jweiland/events2',
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBackendUserFromFixture(1);

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_events2_domain_model_location.csv');

        // Seems that records of ext_tables_static+adt.sql will be included just once for all tests in this class.
        // So, for all tests (except the first one), we have to add the record ourselves.
        $country = $this->getConnectionPool()
            ->getConnectionForTable('static_countries')
            ->select(['*'], 'static_countries', ['uid' => 54])
            ->fetchAssociative();
        if ($country === false) {
            $this->importCSVDataSet(__DIR__ . '/../Fixtures/static_countries.csv');
        }

        $this->geoCodeServiceMock = $this->createMock(GeoCodeService::class);
        $this->messageHelperMock = $this->createMock(MessageHelper::class);
        $this->maps2RegistryMock = $this->createMock(Maps2Registry::class);

        $this->maps2RegistryMock
            ->expects(self::atLeastOnce())
            ->method('getColumnRegistry')
            ->willReturn($this->columnRegistry);

        $this->listenerProviderMock = $this->createMock(ListenerProvider::class);
        $this->listenerProviderMock
            ->expects(self::atLeastOnce())
            ->method('getListenersForEvent')
            ->willReturn([]);

        $this->subject = new CreateMaps2RecordHook(
            $this->geoCodeServiceMock,
            new AddressHelper($this->messageHelperMock, GeneralUtility::makeInstance(ExtConf::class)),
            $this->messageHelperMock,
            new StoragePidHelper($this->messageHelperMock),
            new MapService(
                $this->createMock(ConfigurationManager::class),
                $this->messageHelperMock,
                $this->maps2RegistryMock,
                GeneralUtility::makeInstance(ExtConf::class),
                GeneralUtility::makeInstance(EventDispatcher::class)
            ),
            $this->maps2RegistryMock,
            new EventDispatcher($this->listenerProviderMock)
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->geoCodeServiceMock,
            $this->messageHelperMock,
            $this->mapService,
            $this->eventDispatcher,
            $this->maps2RegistryMock,
            $this->listenerProviderMock
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function processDatamapWithInvalidTableNameWillNotStartRecordCreation(): void
    {
        $this->listenerProviderMock
            ->expects(self::atLeastOnce())
            ->method('getListenersForEvent');

        $this->subject->processDatamap_afterAllOperations(
            new DataHandler()
        );
    }

    /**
     * @test
     */
    public function processDatamapClearsInfoWindowContentCacheIfTableIsPoiCollection(): void
    {
        /** @var FrontendInterface|MockObject $cacheMock */
        $cacheMock = $this->createMock(VariableFrontend::class);
        $cacheMock
            ->expects(self::atLeastOnce())
            ->method('flushByTag')
            ->with('infoWindowUid123');
        $cacheMock
            ->expects(self::atLeastOnce())
            ->method('flushByTag')
            ->with('infoWindowUid234');

        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->with('maps2_cachedhtml')
            ->willReturn($cacheMock);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache');
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManagerMock);

        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'tx_maps2_domain_model_poicollection' => [
                '123' => [
                    'uid' => '123',
                    'pid' => '12',
                    'title' => 'Test',
                    'l10n_parent' => '234',
                ],
            ],
        ];

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }

    /**
     * @test
     */
    public function processDatamapWillBreakIfPoiCollectionIsNotAllowedToBeCreated(): void
    {
        $event = new AllowCreationOfPoiCollectionEvent(
            $this->getConnectionPool()
                ->getConnectionForTable('tx_events2_domain_model_location')
                ->select(['*'], 'tx_events2_domain_model_location', ['uid' => 1])
                ->fetchAssociative(),
            'tx_events2_domain_model_location',
            'tx_maps2_uid',
            $this->columnRegistry['tx_events2_domain_model_location']['tx_maps2_uid'],
            true
        );

        $listenerWithInvalidFlag = static function (AllowCreationOfPoiCollectionEvent $event): void {
            $event->setIsValid(false);
        };

        // First EventListener will be called with foreignLocationRecord and returns false to break further processing
        $this->listenerProviderMock
            ->expects(self::atLeastOnce())
            ->method('getListenersForEvent')
            ->with($event)
            ->willReturn([$listenerWithInvalidFlag]);

        // Second EventListener should not be called. It's true, if break is working
        $this->listenerProviderMock
            ->expects(self::never())
            ->method('getListenersForEvent')
            ->with(self::isInstanceOf(PostProcessPoiCollectionRecordEvent::class));

        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'tx_events2_domain_model_location' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'location' => 'Stefan',
                    'sys_language_uid' => 0,
                    'l10n_parent' => 0,
                    'tx_maps2_uid' => 1,
                ],
            ],
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

        $this->maps2RegistryMock
            ->expects(self::atLeastOnce())
            ->method('getColumnRegistry')
            ->willReturn($columnRegistry);

        // If EventListener was not called, the columnMatch was invalid.
        $this->listenerProviderMock
            ->expects(self::never())
            ->method('getListenersForEvent')
            ->with(self::isInstanceOf(PostProcessPoiCollectionRecordEvent::class));

        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'tx_events2_domain_model_location' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'location' => 'Stefan',
                    'sys_language_uid' => 0,
                    'l10n_parent' => 0,
                    'tx_maps2_uid' => 1,
                ],
            ],
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

        $this->maps2RegistryMock
            ->expects(self::atLeastOnce())
            ->method('getColumnRegistry')
            ->willReturn($columnRegistry);

        if ($isValid) {
            $this->listenerProviderMock
                ->expects(self::atLeastOnce())
                ->method('getListenersForEvent')
                ->with(self::isInstanceOf(PostProcessPoiCollectionRecordEvent::class))
                ->willReturn([]);
        } else {
            $this->listenerProviderMock
                ->expects(self::never())
                ->method('getListenersForEvent')
                ->with(self::isInstanceOf(PostProcessPoiCollectionRecordEvent::class));
        }

        $dataHandler = new DataHandler();
        $dataHandler->datamap = [
            'tx_events2_domain_model_location' => [
                '1' => [
                    'uid' => '1',
                    'pid' => '12',
                    'location' => 'Stefan',
                    'sys_language_uid' => 0,
                    'l10n_parent' => 0,
                    'tx_maps2_uid' => 1,
                ],
            ],
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
                    'sys_language_uid' => 0,
                    'l10n_parent' => 0,
                    'tx_maps2_uid' => 1,
                ],
            ],
        ];

        /** @var Position|MockObject $positionMock */
        $positionMock = $this->createMock(Position::class);
        $positionMock
            ->expects(self::atLeastOnce())
            ->method('getLatitude')
            ->willReturn(12.34);
        $positionMock
            ->expects(self::atLeastOnce())
            ->method('getLongitude')
            ->willReturn(56.78);
        $positionMock
            ->expects(self::atLeastOnce())
            ->method('getFormattedAddress')
            ->willReturn('Echterdinger Straße 57, 70794 Filderstadt, Germany');

        $this->geoCodeServiceMock
            ->expects(self::atLeastOnce())
            ->method('getFirstFoundPositionByAddress')
            ->with('Echterdinger Straße 57 70794 Filderstadt Germany')
            ->willReturn($positionMock);

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }
}

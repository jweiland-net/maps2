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
use JWeiland\Maps2\Helper\AddressHelper;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Helper\StoragePidHelper;
use JWeiland\Maps2\Hook\CreateMaps2RecordHook;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use JWeiland\Maps2\Tca\Maps2Registry;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
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

    protected bool $creationAllowed = true;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    protected $eventDispatcherMock;

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
        parent::markTestIncomplete('Tests requires jweiland/events which is not TYPO3 13 compatible right now');

        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_events2_domain_model_location.csv');

        // Seems that records of ext_tables_static+adt.sql will be included just once for all tests in this class.
        // So, for all tests (except the first one), we have to add the records ourselves.
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
            ->expects(self::any())
            ->method('getColumnRegistry')
            ->willReturn($this->columnRegistry);

        $this->eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $this->updateExpectationsForEventDispatcher();

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
                GeneralUtility::makeInstance(EventDispatcher::class),
            ),
            $this->maps2RegistryMock,
            $this->eventDispatcherMock,
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
            $this->eventDispatcherMock,
        );

        parent::tearDown();
    }

    protected function updateExpectationsForEventDispatcher(): void
    {
        if ($this->creationAllowed) {
            $this->eventDispatcherMock
                ->expects(self::any())
                ->method('dispatch')
                ->willReturnArgument(0);
        } else {
            $creationAllowedEventMock = $this->createMock(AllowCreationOfPoiCollectionEvent::class);
            $creationAllowedEventMock
                ->expects(self::once())
                ->method('isValid')
                ->willReturn(false);

            $this->eventDispatcherMock
                ->expects(self::once())
                ->method('dispatch')
                ->willReturn($creationAllowedEventMock);
        }
    }

    /**
     * @test
     */
    public function processDatamapWithInvalidTableNameWillNotStartRecordCreation(): void
    {
        $this->eventDispatcherMock
            ->expects(self::never())
            ->method('dispatch');

        $this->subject->processDatamap_afterAllOperations(
            new DataHandler(),
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
            ->with(self::logicalOr(
                self::equalTo('infoWindowUid123'),
                self::equalTo('infoWindowUid234'),
            ));

        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects(self::atLeastOnce())
            ->method('getCache')
            ->willReturnMap([
                ['maps2_cachedhtml', $cacheMock],
                ['runtime', null],
            ]);
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
        $this->creationAllowed = false;

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
        $this->creationAllowed = false;

        $columnRegistry = $this->columnRegistry;
        $columnRegistry['tx_events2_domain_model_location']['tx_maps2_uid']['columnMatch']['pid'] = 432;

        $this->maps2RegistryMock
            ->expects(self::atLeastOnce())
            ->method('getColumnRegistry')
            ->willReturn($columnRegistry);

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
    public static function dataProcessorForExpressions(): array
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
        bool $isValid,
    ): void {
        $this->creationAllowed = $isValid;

        $columnRegistry = $this->columnRegistry;
        $columnRegistry['tx_events2_domain_model_location']['tx_maps2_uid']['columnMatch'] = $columnMatch;

        $this->maps2RegistryMock
            ->expects(self::atLeastOnce())
            ->method('getColumnRegistry')
            ->willReturn($columnRegistry);

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

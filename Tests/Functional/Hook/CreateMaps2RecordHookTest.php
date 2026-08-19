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
use JWeiland\Maps2\Helper\AddressHelper;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Helper\StoragePidHelper;
use JWeiland\Maps2\Hook\CreateMaps2RecordHook;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use JWeiland\Maps2\Tca\ColumnRegistration;
use JWeiland\Maps2\Tca\ColumnRegistrationStorage;
use JWeiland\Maps2\Tca\ForeignColumn;
use JWeiland\Maps2\Tca\SynchronizeColumn;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional test for CreateMaps2RecordHook
 */
class CreateMaps2RecordHookTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        __DIR__ . '/../Fixtures/Extensions/address',
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_address_domain_model_address.csv');
    }

    #[Test]
    public function processDatamapClearsInfoWindowContentCacheIfTableIsPoiCollection(): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
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

        $cacheMock = $this->createMock(FrontendInterface::class);
        $cacheMock
            ->expects($this->atLeastOnce())
            ->method('flushByTag')
            ->willReturnMap([
                ['infoWindowUid123'],
                ['infoWindowUid234'],
            ]);

        $cacheManagerMock = $this->createMock(CacheManager::class);
        $cacheManagerMock
            ->expects($this->atLeastOnce())
            ->method('getCache')
            ->willReturn($cacheMock);

        $subject = new CreateMaps2RecordHook(
            $this->get(GeoCodeService::class),
            $this->get(AddressHelper::class),
            $this->get(MessageHelper::class),
            $this->get(StoragePidHelper::class),
            $this->get(MapService::class),
            new ColumnRegistrationStorage(),
            $this->get(EventDispatcherInterface::class),
            $this->get(TcaSchemaFactory::class),
            $cacheManagerMock,
            $this->getConnectionPool(),
        );

        $subject->processDatamap_afterAllOperations($dataHandler);
    }

    #[Test]
    public function processDatamapCreatesNewPoiCollection(): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->datamap = [
            'tx_address_domain_model_address' => [
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
            ->expects($this->atLeastOnce())
            ->method('getLatitude')
            ->willReturn(12.34);
        $positionMock
            ->expects($this->atLeastOnce())
            ->method('getLongitude')
            ->willReturn(56.78);
        $positionMock
            ->expects($this->atLeastOnce())
            ->method('getFormattedAddress')
            ->willReturn('Echterdinger Straße 57, 70794 Filderstadt, Germany');

        $geoCodeServiceMock = $this->createMock(GeoCodeService::class);
        $geoCodeServiceMock
            ->expects($this->atLeastOnce())
            ->method('getFirstFoundPositionByAddress')
            ->with('Echterdinger Straße 57 70794 Filderstadt Germany')
            ->willReturn($positionMock);

        $subject = new CreateMaps2RecordHook(
            $geoCodeServiceMock,
            $this->get(AddressHelper::class),
            $this->get(MessageHelper::class),
            $this->get(StoragePidHelper::class),
            $this->get(MapService::class),
            new ColumnRegistrationStorage([
                new ColumnRegistration(
                    tableName: 'tx_address_domain_model_address',
                    columnName: 'tx_maps2_uid',
                    addressColumns: ['street', 'zip', 'city'],
                    countryColumn: 'country',
                ),
            ]),
            $this->get(EventDispatcherInterface::class),
            $this->get(TcaSchemaFactory::class),
            $this->get(CacheManager::class),
            $this->getConnectionPool(),
        );

        $subject->processDatamap_afterAllOperations($dataHandler);
    }

    #[Test]
    public function synchronizeColumnsFromForeignRecordWithPoiCollectionResolvesComposedForeignColumn(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_maps2_domain_model_poicollection.csv');

        $addressQueryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_address_domain_model_address');
        $foreignLocationRecord = $addressQueryBuilder
            ->select('*')
            ->from('tx_address_domain_model_address')
            ->where(
                $addressQueryBuilder->expr()->eq(
                    'uid',
                    $addressQueryBuilder->createNamedParameter(1, Connection::PARAM_INT),
                ),
            )
            ->executeQuery()
            ->fetchAssociative();

        self::assertIsArray($foreignLocationRecord);

        $columnRegistration = new ColumnRegistration(
            tableName: 'tx_address_domain_model_address',
            columnName: 'tx_maps2_uid',
            addressColumns: ['street', 'zip', 'city'],
            synchronizeColumns: [
                new SynchronizeColumn(
                    foreignColumn: ForeignColumn::createFromConfiguration([
                        'type' => 'coalesce',
                        'columns' => [
                            'company',
                            [
                                'type' => 'concat',
                                'columns' => ['first_name', 'last_name'],
                                'glue' => ' ',
                            ],
                        ],
                    ]),
                    poiCollectionColumnName: 'title',
                ),
            ],
        );

        $subject = new CreateMaps2RecordHook(
            $this->get(GeoCodeService::class),
            $this->get(AddressHelper::class),
            $this->get(MessageHelper::class),
            $this->get(StoragePidHelper::class),
            $this->get(MapService::class),
            new ColumnRegistrationStorage(),
            $this->get(EventDispatcherInterface::class),
            $this->get(TcaSchemaFactory::class),
            $this->get(CacheManager::class),
            $this->getConnectionPool(),
        );

        self::assertTrue(
            $subject->synchronizeColumnsFromForeignRecordWithPoiCollection(
                $foreignLocationRecord,
                'tx_address_domain_model_address',
                'tx_maps2_uid',
                $columnRegistration,
            ),
        );

        $poiCollectionQueryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_maps2_domain_model_poicollection');
        $poiCollectionTitle = $poiCollectionQueryBuilder
            ->select('title')
            ->from('tx_maps2_domain_model_poicollection')
            ->where(
                $poiCollectionQueryBuilder->expr()->eq(
                    'uid',
                    $poiCollectionQueryBuilder->createNamedParameter(1, Connection::PARAM_INT),
                ),
            )
            ->executeQuery()
            ->fetchOne();

        self::assertSame('Stefan Froemken', $poiCollectionTitle);
    }
}

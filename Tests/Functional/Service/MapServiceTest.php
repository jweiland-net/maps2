<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Service;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Event\PreAddForeignRecordEvent;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Service\MapService;
use JWeiland\Maps2\Tca\Maps2Registry;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test MapService
 */
class MapServiceTest extends FunctionalTestCase
{
    protected MapService $subject;

    /**
     * @var ConfigurationManagerInterface|MockObject
     */
    protected $configurationManagerMock;

    /**
     * @var MessageHelper|MockObject
     */
    protected $messageHelperMock;

    /**
     * @var Maps2Registry|MockObject
     */
    protected $maps2RegistryMock;

    /**
     * @var ExtConf|MockObject
     */
    protected $extConfMock;

    /**
     * @var EventDispatcher|MockObject
     */
    protected $eventDispatcherMock;

    protected array $testExtensionsToLoad = [
        'jweiland/events2',
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_events2_domain_model_location.csv');

        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://www.example.com/'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $this->messageHelperMock = $this->createMock(MessageHelper::class);
        $this->maps2RegistryMock = $this->createMock(Maps2Registry::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcher::class);

        // Override partials path to prevent using f:format.html VH. It checks against applicationType which is not present in TYPO3 10.
        $this->configurationManagerMock = $this->createMock(ConfigurationManager::class);

        // Replace default template to prevent calling cache VHs. They check against FE
        $this->extConfMock = $this->createMock(ExtConf::class);

        $this->subject = new MapService(
            $this->configurationManagerMock,
            $this->messageHelperMock,
            $this->maps2RegistryMock,
            $this->extConfMock,
            $this->eventDispatcherMock
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->configurationManagerMock,
            $this->messageHelperMock,
            $this->maps2RegistryMock,
            $this->extConfMock,
            $this->eventDispatcherMock
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderInfoWindowWillLoadTemplatePathFromTypoScript(): void
    {
        $this->configurationManagerMock
            ->expects(self::atLeastOnce())
            ->method('getConfiguration')
            ->willReturnMap([
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                    'Maps2',
                    'Maps2',
                    [
                        'view' => [
                            'layoutRootPaths' => [],
                            'partialRootPaths' => [
                                'EXT:maps2/Tests/Functional/Fixtures/Partials',
                            ],
                        ],
                    ],
                ],
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                    'Maps2',
                    'Maps2',
                    [
                        'infoWindowContentTemplatePath' => 'EXT:maps2/Resources/Private/Templates/InfoWindowContentNoCache.html',
                    ],
                ],
            ]);

        $this->extConfMock
            ->expects(self::never())
            ->method('getInfoWindowContentTemplatePath');

        $poiCollection = new PoiCollection();
        $poiCollection->setTitle('Test 123');

        self::assertStringContainsString(
            'Test 123',
            $this->subject->renderInfoWindow($poiCollection)
        );
    }

    /**
     * @test
     */
    public function renderInfoWindowWillRenderPoiCollectionTitle(): void
    {
        $this->configurationManagerMock
            ->expects(self::atLeastOnce())
            ->method('getConfiguration')
            ->willReturnMap([
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                    'Maps2',
                    'Maps2',
                    [
                        'view' => [
                            'layoutRootPaths' => [],
                            'partialRootPaths' => [
                                'EXT:maps2/Tests/Functional/Fixtures/Partials',
                            ],
                        ],
                    ],
                ],
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                    'Maps2',
                    'Maps2',
                    [],
                ],
            ]);

        $this->extConfMock
            ->expects(self::atLeastOnce())
            ->method('getInfoWindowContentTemplatePath')
            ->willReturn('EXT:maps2/Resources/Private/Templates/InfoWindowContentNoCache.html');

        $poiCollection = new PoiCollection();
        $poiCollection->setTitle('Test 123');

        self::assertStringContainsString(
            'Test 123',
            $this->subject->renderInfoWindow($poiCollection)
        );
    }

    /**
     * @test
     */
    public function renderInfoWindowWillRenderPoiCollectionAddress(): void
    {
        $this->configurationManagerMock
            ->expects(self::atLeastOnce())
            ->method('getConfiguration')
            ->willReturnMap([
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                    'Maps2',
                    'Maps2',
                    [
                        'view' => [
                            'layoutRootPaths' => [],
                            'partialRootPaths' => [
                                'EXT:maps2/Tests/Functional/Fixtures/Partials',
                            ],
                        ],
                    ],
                ],
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                    'Maps2',
                    'Maps2',
                    [],
                ],
            ]);

        $this->extConfMock
            ->expects(self::atLeastOnce())
            ->method('getInfoWindowContentTemplatePath')
            ->willReturn('EXT:maps2/Resources/Private/Templates/InfoWindowContentNoCache.html');

        $poiCollection = new PoiCollection();
        $poiCollection->setTitle('jweiland.net');
        $poiCollection->setAddress('Echterdinger Straße 57, Gebäude 9, 70794 Filderstadt, Germany');

        $renderedContent = $this->subject->renderInfoWindow($poiCollection);

        self::assertStringContainsString(
            'Echterdinger Straße 57',
            $renderedContent
        );
        self::assertStringContainsString(
            'Gebäude 9',
            $renderedContent
        );
        self::assertStringContainsString(
            '70794 Filderstadt',
            $renderedContent
        );

        self::assertStringNotContainsString(
            'Germany',
            $renderedContent
        );
    }

    /**
     * @test
     */
    public function renderInfoWindowWillRenderPoiCollectionInfoWindowContent(): void
    {
        $this->configurationManagerMock
            ->expects(self::atLeastOnce())
            ->method('getConfiguration')
            ->willReturnMap([
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                    'Maps2',
                    'Maps2',
                    [
                        'view' => [
                            'layoutRootPaths' => [],
                            'partialRootPaths' => [
                                'EXT:maps2/Tests/Functional/Fixtures/Partials',
                            ],
                        ],
                    ],
                ],
                [
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                    'Maps2',
                    'Maps2',
                    [],
                ],
            ]);

        $this->extConfMock
            ->expects(self::atLeastOnce())
            ->method('getInfoWindowContentTemplatePath')
            ->willReturn('EXT:maps2/Resources/Private/Templates/InfoWindowContentNoCache.html');

        $poiCollection = new PoiCollection();
        $poiCollection->setTitle('Test 123');
        $poiCollection->setInfoWindowContent('Hello all together');

        self::assertStringContainsString(
            'Hello all together',
            $this->subject->renderInfoWindow($poiCollection)
        );
    }

    /**
     * @test
     */
    public function createNewPoiCollectionWithEmptyLatitudeReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->createNewPoiCollection(
                1,
                new Position()
            )
        );
    }

    /**
     * @test
     */
    public function createNewPoiCollectionWillCreateNewPoiCollectionRecord(): void
    {
        $position = new Position();
        $position->setLatitude(51.4);
        $position->setLongitude(7.4);
        $position->setFormattedAddress('Echterdinger Straße 57, 70794 Filderstadt, Germany');

        self::assertSame(
            1,
            $this->subject->createNewPoiCollection(
                1,
                $position
            )
        );
    }

    /**
     * @test
     */
    public function createNewPoiCollectionWithOverrideWillCreateNewPoiCollectionRecord(): void
    {
        $position = new Position();
        $position->setLatitude(51.4);
        $position->setLongitude(7.4);
        $position->setFormattedAddress('Echterdinger Straße 57, 70794 Filderstadt, Germany');

        $poiCollectionUid = $this->subject->createNewPoiCollection(
            1,
            $position,
            [
                'longitude' => 12.3,
            ]
        );

        $poiCollectionRecord = $this->getConnectionPool()
            ->getConnectionForTable('tx_maps2_domain_model_poicollection')
            ->select(['*'], 'tx_maps2_domain_model_poicollection', ['uid' => $poiCollectionUid])
            ->fetchAssociative();

        $poiCollectionRecord = array_intersect_key(
            $poiCollectionRecord,
            ['uid' => 1, 'longitude' => 1]
        );

        self::assertSame(
            [
                'uid' => 1,
                'longitude' => 12.3,
            ],
            $poiCollectionRecord
        );
    }

    /**
     * @test
     */
    public function assignPoiCollectionToForeignRecordWithEmptyPoiCollectionUidAddsFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('PoiCollection UID can not be empty'),
                'PoiCollection empty',
                ContextualFeedbackSeverity::ERROR
            );

        $foreignRecord = [
            'uid' => 1,
        ];

        $this->subject->assignPoiCollectionToForeignRecord(
            0,
            $foreignRecord,
            'tx_events2_domain_model_location'
        );
    }

    /**
     * @test
     */
    public function assignPoiCollectionToForeignRecordWithEmptyForeignRecordAddsFlashMessages(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->willReturnMap([
                [
                    self::stringContains('Foreign record can not be empty'),
                    'Foreign record empty',
                    ContextualFeedbackSeverity::ERROR,
                ],
                [
                    self::stringContains('Foreign record must have the array key "uid" which is currently not present'),
                    'UID not filled',
                    ContextualFeedbackSeverity::ERROR,
                ],
            ]);

        $foreignRecord = [];

        $this->subject->assignPoiCollectionToForeignRecord(
            1,
            $foreignRecord,
            'tx_events2_domain_model_location'
        );
    }

    /**
     * @test
     */
    public function assignPoiCollectionToForeignRecordWithEmptyForeignTableNameAddsFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('Foreign table name is a must have value'),
                'Foreign table name empty',
                ContextualFeedbackSeverity::ERROR
            );

        $foreignRecord = [
            'uid' => 1,
        ];

        $this->subject->assignPoiCollectionToForeignRecord(
            1,
            $foreignRecord,
            ''
        );
    }

    /**
     * @test
     */
    public function assignPoiCollectionToForeignRecordWithEmptyForeignFieldNameAddsFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('Foreign field name is a must have value'),
                'Foreign field name empty',
                ContextualFeedbackSeverity::ERROR
            );

        $foreignRecord = [
            'uid' => 1,
        ];

        $this->subject->assignPoiCollectionToForeignRecord(
            1,
            $foreignRecord,
            'tx_events2_domain_model_location',
            '  '
        );
    }

    /**
     * @test
     */
    public function assignPoiCollectionToForeignRecordWithInvalidTableNameAddsFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('Table "invalidTable" is not configured in TCA'),
                'Table not found',
                ContextualFeedbackSeverity::ERROR
            );

        $foreignRecord = [
            'uid' => 1,
        ];

        $this->subject->assignPoiCollectionToForeignRecord(
            1,
            $foreignRecord,
            'invalidTable'
        );
    }

    /**
     * @test
     */
    public function assignPoiCollectionToForeignRecordWithInvalidFieldNameAddsFlashMessage(): void
    {
        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                self::stringContains('Field "invalidField" is not configured in TCA'),
                'Field not found',
                ContextualFeedbackSeverity::ERROR
            );

        $foreignRecord = [
            'uid' => 1,
        ];

        $this->subject->assignPoiCollectionToForeignRecord(
            1,
            $foreignRecord,
            'tx_events2_domain_model_location',
            'invalidField'
        );
    }

    /**
     * @test
     */
    public function assignPoiCollectionToForeignRecordWillUpdateForeignRecord(): void
    {
        $position = new Position();
        $position->setLatitude(54.1);
        $position->setLongitude(7.3);
        $position->setFormattedAddress('Echterdinger Straße 57, 70794 Filderstadt');

        $newUid = $this->subject->createNewPoiCollection(
            12,
            $position
        );

        $this->messageHelperMock
            ->expects(self::never())
            ->method('addFlashMessage');

        $foreignRecord = [
            'uid' => 1,
        ];

        $this->subject->assignPoiCollectionToForeignRecord(
            1,
            $foreignRecord,
            'tx_events2_domain_model_location'
        );

        self::assertSame(
            $foreignRecord['tx_maps2_uid'],
            $newUid
        );

        $locationRecord = $this->getConnectionPool()
            ->getConnectionForTable('tx_events2_domain_model_location')
            ->select(['*'], 'tx_events2_domain_model_location', ['uid' => 1])
            ->fetchAssociative();

        self::assertSame(
            $locationRecord['tx_maps2_uid'],
            $newUid
        );
    }

    public function addForeignRecordsToPoiCollectionWithEmptyRegistryWillNotAddForeignRecords(): void
    {
        $this->maps2RegistryMock
            ->expects(self::atLeastOnce())
            ->method('getColumnRegistry')
            ->willReturn([]);

        /** @var PoiCollection|MockObject $poiCollectionMock */
        $poiCollectionMock = $this->createMock(PoiCollection::class);
        $poiCollectionMock
            ->expects(self::never())
            ->method('addForeignRecord');

        $this->subject->addForeignRecordsToPoiCollection($poiCollectionMock);
    }

    public function addForeignRecordsToPoiCollectionWithEmptyPoiCollectionUidWillNotAddForeignRecords(): void
    {
        $this->maps2RegistryMock
            ->expects(self::atLeastOnce())
            ->method('getColumnRegistry')
            ->willReturn(['foo' => 'bar']);

        /** @var PoiCollection|MockObject $poiCollectionMock */
        $poiCollectionMock = $this->createMock(PoiCollection::class);
        $poiCollectionMock
            ->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(0);
        $poiCollectionMock
            ->expects(self::never())
            ->method('addForeignRecord');

        $this->subject->addForeignRecordsToPoiCollection($poiCollectionMock);
    }

    public function addForeignRecordsToPoiCollectionWillAddForeignRecord(): void
    {
        $this->maps2RegistryMock
            ->expects(self::atLeastOnce())
            ->method('getColumnRegistry')
            ->willReturn([
                'tx_events2_domain_model_location' => [
                    'tx_maps2_uid' => [],
                ],
            ]);

        /** @var PoiCollection|MockObject $poiCollectionMock */
        $poiCollectionMock = $this->createMock(PoiCollection::class);
        $poiCollectionMock
            ->expects(self::atLeastOnce())
            ->method('addForeignRecord');

        $position = new Position();
        $position->setLatitude(54.1);
        $position->setLongitude(7.3);
        $position->setFormattedAddress('Echterdinger Straße 57, 70794 Filderstadt');

        $newUid = $this->subject->createNewPoiCollection(
            12,
            $position
        );
        $foreignRecord = ['uid' => 1];
        $this->subject->assignPoiCollectionToForeignRecord(
            $newUid,
            $foreignRecord,
            'tx_events2_domain_model_location'
        );

        $event = new PreAddForeignRecordEvent(
            $foreignRecord,
            'tx_events2_domain_model_location',
            'tx_maps2_uid'
        );

        $this->eventDispatcherMock
            ->expects(self::atLeastOnce())
            ->method('dispatch')
            ->willReturn($event);

        $this->subject->addForeignRecordsToPoiCollection($poiCollectionMock);
    }
}

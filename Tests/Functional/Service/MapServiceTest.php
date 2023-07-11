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
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test MapService
 */
class MapServiceTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected MapService $subject;

    /**
     * @var ConfigurationManagerInterface|ObjectProphecy
     */
    protected $configurationManagerProphecy;

    /**
     * @var MessageHelper|ObjectProphecy
     */
    protected $messageHelperProphecy;

    /**
     * @var Maps2Registry|ObjectProphecy
     */
    protected $maps2RegistryProphecy;

    /**
     * @var ExtConf|ObjectProphecy
     */
    protected $extConfProphecy;

    /**
     * @var EventDispatcher|ObjectProphecy
     */
    protected $eventDispatcherProphecy;

    /**
     * @var EnvironmentService|ObjectProphecy
     */
    protected $environmentServiceProphecy;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/events2',
        'typo3conf/ext/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/../Fixtures/tx_events2_domain_model_location.xml');

        $this->messageHelperProphecy = $this->prophesize(MessageHelper::class);
        $this->maps2RegistryProphecy = $this->prophesize(Maps2Registry::class);
        $this->eventDispatcherProphecy = $this->prophesize(EventDispatcher::class);

        // Override partials path to prevent using f:format.html VH. It checks against applicationType which is not present in TYPO3 10.
        $this->configurationManagerProphecy = $this->prophesize(ConfigurationManager::class);
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'Maps2',
                'Maps2'
            )
            ->willReturn([
                'view' => [
                    'layoutRootPaths' => [],
                    'partialRootPaths' => [
                        'EXT:maps2/Tests/Functional/Fixtures/Partials',
                    ],
                ],
            ]);
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2',
                'Maps2'
            )
            ->willReturn([]);

        // Replace default template to prevent calling cache VHs. They check against FE
        $this->extConfProphecy = $this->prophesize(ExtConf::class);
        $this->extConfProphecy
            ->getInfoWindowContentTemplatePath()
            ->willReturn('EXT:maps2/Resources/Private/Templates/InfoWindowContentNoCache.html');

        $this->subject = new MapService(
            $this->configurationManagerProphecy->reveal(),
            $this->messageHelperProphecy->reveal(),
            $this->maps2RegistryProphecy->reveal(),
            $this->extConfProphecy->reveal(),
            $this->eventDispatcherProphecy->reveal()
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->configurationManagerProphecy,
            $this->messageHelperProphecy,
            $this->maps2RegistryProphecy,
            $this->extConfProphecy,
            $this->eventDispatcherProphecy,
            $this->environmentServiceProphecy
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderInfoWindowWillLoadTemplatePathFromTypoScript(): void
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2',
                'Maps2'
            )
            ->shouldBeCalled()
            ->willReturn([
                'infoWindowContentTemplatePath' => 'EXT:maps2/Resources/Private/Templates/InfoWindowContentNoCache.html',
            ]);

        $this->extConfProphecy
            ->getInfoWindowContentTemplatePath()
            ->shouldNotBeCalled();

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

        $this->subject->createNewPoiCollection(
            1,
            $position,
            [
                'hidden' => 1,
                'longitude' => 12.3,
            ]
        );

        $poiCollectionRecord = $this->getDatabaseConnection()->selectSingleRow(
            '*',
            'tx_maps2_domain_model_poicollection',
            'uid=1'
        );

        $poiCollectionRecord = array_intersect_key(
            $poiCollectionRecord,
            ['uid' => 1, 'hidden' => 1, 'longitude' => 1]
        );

        self::assertSame(
            [
                'uid' => 1,
                'hidden' => 1,
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
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::any(),
                Argument::not('PoiCollection empty')
            )
            ->shouldNotBeCalled();

        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('PoiCollection UID can not be empty'),
                Argument::exact('PoiCollection empty'),
                AbstractMessage::ERROR
            )
            ->shouldBeCalled();

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
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('Foreign record can not be empty'),
                Argument::exact('Foreign record empty'),
                AbstractMessage::ERROR
            )
            ->shouldBeCalled();

        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('Foreign record must have the array key "uid" which is currently not present'),
                Argument::exact('UID not filled'),
                AbstractMessage::ERROR
            )
            ->shouldBeCalled();

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
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::any(),
                Argument::not('Foreign table name empty')
            )
            ->shouldNotBeCalled();

        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('Foreign table name is a must have value'),
                Argument::exact('Foreign table name empty'),
                AbstractMessage::ERROR
            )
            ->shouldBeCalled();

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
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::any(),
                Argument::not('Foreign field name empty')
            )
            ->shouldNotBeCalled();

        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('Foreign field name is a must have value'),
                Argument::exact('Foreign field name empty'),
                AbstractMessage::ERROR
            )
            ->shouldBeCalled();

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
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('Table "invalidTable" is not configured in TCA'),
                Argument::exact('Table not found'),
                AbstractMessage::ERROR
            )
            ->shouldBeCalled();

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
        $this->messageHelperProphecy
            ->addFlashMessage(
                Argument::containingString('Field "invalidField" is not configured in TCA'),
                Argument::exact('Field not found'),
                AbstractMessage::ERROR
            )
            ->shouldBeCalled();

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

        $this->messageHelperProphecy
            ->addFlashMessage(Argument::cetera())
            ->shouldNotBeCalled();

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

        $locationRecord = $this->getDatabaseConnection()->selectSingleRow(
            '*',
            'tx_events2_domain_model_location',
            'uid=1'
        );

        self::assertSame(
            $locationRecord['tx_maps2_uid'],
            $newUid
        );
    }

    public function addForeignRecordsToPoiCollectionWithEmptyRegistryWillNotAddForeignRecords(): void
    {
        $this->maps2RegistryProphecy
            ->getColumnRegistry()
            ->shouldBeCalled()
            ->willReturn([]);

        /** @var PoiCollection|ObjectProphecy $poiCollectionProphecy */
        $poiCollectionProphecy = $this->prophesize(PoiCollection::class);
        $poiCollectionProphecy
            ->addForeignRecord(Argument::any())
            ->shouldNotBeCalled();

        $this->subject->addForeignRecordsToPoiCollection($poiCollectionProphecy->reveal());
    }

    public function addForeignRecordsToPoiCollectionWithEmptyPoiCollectionUidWillNotAddForeignRecords(): void
    {
        $this->maps2RegistryProphecy
            ->getColumnRegistry()
            ->shouldBeCalled()
            ->willReturn(['foo' => 'bar']);

        /** @var PoiCollection|ObjectProphecy $poiCollectionProphecy */
        $poiCollectionProphecy = $this->prophesize(PoiCollection::class);
        $poiCollectionProphecy
            ->getUid()
            ->shouldBeCalled()
            ->willReturn(0);
        $poiCollectionProphecy
            ->addForeignRecord(Argument::any())
            ->shouldNotBeCalled();

        $this->subject->addForeignRecordsToPoiCollection($poiCollectionProphecy->reveal());
    }

    public function addForeignRecordsToPoiCollectionWillAddForeignRecord(): void
    {
        $this->maps2RegistryProphecy
            ->getColumnRegistry()
            ->shouldBeCalled()
            ->willReturn([
                'tx_events2_domain_model_location' => [
                    'tx_maps2_uid' => [],
                ],
            ]);

        /** @var PoiCollection|ObjectProphecy $poiCollectionProphecy */
        $poiCollectionProphecy = $this->prophesize(PoiCollection::class);
        $poiCollectionProphecy
            ->addForeignRecord(Argument::any())
            ->shouldBeCalled();

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

        $this->eventDispatcherProphecy
            ->dispatch(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn($event);

        $this->subject->addForeignRecordsToPoiCollection($poiCollectionProphecy->reveal());
    }
}

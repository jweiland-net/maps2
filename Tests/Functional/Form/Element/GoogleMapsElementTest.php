<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Form\Element;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Form\Element\GoogleMapsElement;
use JWeiland\Maps2\Helper\MessageHelper;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class GoogleMapsElementTest
 */
class GoogleMapsElementTest extends FunctionalTestCase
{
    use ProphecyTrait;

    /**
     * @var GoogleMapsElement
     */
    protected $subject;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var ObjectManager|ObjectProphecy
     */
    protected $objectManager;

    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var StandaloneView|ObjectProphecy
     */
    protected $view;

    /**
     * @var PageRenderer|ObjectProphecy
     */
    protected $pageRenderer;

    /**
     * @var PoiCollectionRepository|ObjectProphecy
     */
    protected $poiCollectionRepository;

    /**
     * @var MessageHelper|ObjectProphecy
     */
    protected $messageHelper;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'databaseRow' => [
                'uid' => '123',
                'pid' => '321',
                'address' => 'Echterdinger Str. 57, 70794 Filderstadt',
                'collection_type' => [
                    0 => 'Point'
                ]
            ]
        ];
        $this->poiCollectionRepository = $this->prophesize(PoiCollectionRepository::class);
        GeneralUtility::setSingletonInstance(
            PoiCollectionRepository::class,
            $this->poiCollectionRepository->reveal()
        );

        $this->objectManager = $this->prophesize(ObjectManager::class);
        $this->objectManager
            ->get(PoiCollectionRepository::class)
            ->shouldBeCalled()
            ->willReturn($this->poiCollectionRepository->reveal());
        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManager->reveal());

        $this->extConf = new ExtConf([]);
        GeneralUtility::setSingletonInstance(ExtConf::class, $this->extConf);
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'test@123';

        $this->view = $this->prophesize(StandaloneView::class);
        GeneralUtility::addInstance(StandaloneView::class, $this->view->reveal());

        $this->pageRenderer = $this->prophesize(PageRenderer::class);
        GeneralUtility::setSingletonInstance(PageRenderer::class, $this->pageRenderer->reveal());

        $this->messageHelper = $this->prophesize(MessageHelper::class);
        GeneralUtility::addInstance(MessageHelper::class, $this->messageHelper->reveal());

        /** @var IconFactory|ObjectProphecy $iconFactoryProphecy */
        $iconFactoryProphecy = $this->prophesize(IconFactory::class);
        GeneralUtility::addInstance(IconFactory::class, $iconFactoryProphecy->reveal());

        $this->subject = new GoogleMapsElement(
            GeneralUtility::makeInstance(NodeFactory::class),
            $this->data
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->objectManager,
            $this->extConf,
            $this->view,
            $this->pageRenderer,
            $this->poiCollectionRepository
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderWillCleanUpCurrentRecord()
    {
        $this->poiCollectionRepository->findByUid(123)->shouldBeCalled()->willReturn(new PoiCollection());

        $config = [
            'latitude' => 0,
            'longitude' => 0,
            'address' => 'Echterdinger Str. 57, 70794 Filderstadt',
            'collectionType' => 'Point', // this value was an array before
            'uid' => 123, // this value was string before
            'hash' => '03134cbb9d4b445f6e0a99d7ed7bf267356efc47',
        ];
        $this->view
            ->setTemplatePathAndFilename(
                Argument::containingString('Resources/Private/Templates/Tca/GoogleMaps.html')
            )
            ->shouldBeCalled();
        $this->view
            ->assign('config', json_encode($config))
            ->shouldBeCalled();
        $this->view
            ->assign('extConf', Argument::any())
            ->shouldBeCalled();
        $this->view->render()->shouldBeCalled();

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderWillAddRadiusToConfigArray()
    {
        $poiCollection = new PoiCollection();
        $poiCollection->setCollectionType('Radius');
        $poiCollection->setRadius(250);

        $this->poiCollectionRepository->findByUid(123)->shouldBeCalled()->willReturn($poiCollection);

        $config = [
            'latitude' => 0,
            'longitude' => 0,
            'radius' => 250,
            'address' => 'Echterdinger Str. 57, 70794 Filderstadt',
            'collectionType' => 'Point', // this value was an array before
            'uid' => 123, // this value was string before
            'hash' => '03134cbb9d4b445f6e0a99d7ed7bf267356efc47',
        ];
        $this->view
            ->setTemplatePathAndFilename(
                Argument::containingString('Resources/Private/Templates/Tca/GoogleMaps.html')
            )
            ->shouldBeCalled();
        $this->view
            ->assign('config', json_encode($config))
            ->shouldBeCalled();
        $this->view
            ->assign('extConf', Argument::any())
            ->shouldBeCalled();
        $this->view->render()->shouldBeCalled();

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderWillAddLatAndLngToConfigArray()
    {
        $poiCollection = new PoiCollection();
        $poiCollection->setCollectionType('Point');
        $poiCollection->setLatitude(0.123);
        $poiCollection->setLongitude(54.321);

        $this->poiCollectionRepository->findByUid(123)->shouldBeCalled()->willReturn($poiCollection);

        $config = [
            'latitude' => 0.123,
            'longitude' => 54.321,
            'address' => 'Echterdinger Str. 57, 70794 Filderstadt',
            'collectionType' => 'Point', // this value was an array before
            'uid' => 123, // this value was string before
            'hash' => '03134cbb9d4b445f6e0a99d7ed7bf267356efc47',
        ];
        $this->view
            ->setTemplatePathAndFilename(
                Argument::containingString('Resources/Private/Templates/Tca/GoogleMaps.html')
            )
            ->shouldBeCalled();
        $this->view
            ->assign('config', json_encode($config))
            ->shouldBeCalled();
        $this->view
            ->assign('extConf', Argument::any())
            ->shouldBeCalled();
        $this->view->render()->shouldBeCalled();

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderWillAddRequireJsModule()
    {
        $this->poiCollectionRepository->findByUid(123)->shouldBeCalled()->willReturn(new PoiCollection());

        $result = $this->subject->render();
        self::assertSame(
            [
                'TYPO3/CMS/Maps2/GoogleMapsModule'
            ],
            $result['requireJsModules']
        );
    }
}

<?php
namespace JWeiland\Maps2\Tests\Unit\Form\Element;

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

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Form\Element\GoogleMapsElement;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class GoogleMapsElementTest
 */
class GoogleMapsElementTest extends UnitTestCase
{
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
     * @var ExtConf|ObjectProphecy
     */
    protected $extConf;

    /**
     * @var HashService|ObjectProphecy
     */
    protected $hashService;

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
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
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
        $this->objectManager = $this->prophesize(ObjectManager::class);

        $this->extConf = $this->prophesize(ExtConf::class);
        GeneralUtility::setSingletonInstance(
            ExtConf::class,
            $this->extConf->reveal()
        );

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'test@123';
        $this->hashService = new HashService();

        $this->view = $this->prophesize(StandaloneView::class);
        GeneralUtility::addInstance(
            StandaloneView::class,
            $this->view->reveal()
        );

        $this->pageRenderer = $this->prophesize(PageRenderer::class);
        GeneralUtility::setSingletonInstance(
            PageRenderer::class,
            $this->pageRenderer->reveal()
        );

        $this->poiCollectionRepository = $this->prophesize(PoiCollectionRepository::class);
        GeneralUtility::setSingletonInstance(
            PoiCollectionRepository::class,
            $this->poiCollectionRepository->reveal()
        );

        $this->objectManager->get(ExtConf::class)->shouldBeCalled()->willReturn($this->extConf->reveal());
        $this->objectManager->get(HashService::class)->shouldBeCalled()->willReturn($this->hashService);
        $this->objectManager->get(StandaloneView::class)->shouldBeCalled()->willReturn($this->view->reveal());
        $this->objectManager->get(PageRenderer::class)->shouldBeCalled()->willReturn($this->pageRenderer->reveal());
        $this->objectManager->get(PoiCollectionRepository::class)->shouldBeCalled()->willReturn($this->poiCollectionRepository->reveal());

        GeneralUtility::setSingletonInstance(
            ObjectManager::class,
            $this->objectManager->reveal()
        );

        /** @var NodeFactory|ObjectProphecy $nodeFactory */
        $nodeFactory = $this->prophesize(NodeFactory::class);
        $this->subject = new GoogleMapsElement(
            $nodeFactory->reveal(),
            $this->data
        );
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset(
            $this->subject,
            $this->objectManager,
            $this->extConf,
            $this->hashService,
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
            ->setTemplatePathAndFilename(Argument::containingString('Resources/Private/Templates/Tca/GoogleMaps.html'))
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
            ->setTemplatePathAndFilename(Argument::containingString('Resources/Private/Templates/Tca/GoogleMaps.html'))
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
            ->setTemplatePathAndFilename(Argument::containingString('Resources/Private/Templates/Tca/GoogleMaps.html'))
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
        $this->assertSame(
            [0 => 'TYPO3/CMS/Maps2/GoogleMapsModule'],
            $result['requireJsModules']
        );
    }
}

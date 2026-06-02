<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Domain\Model;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\Category;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Helper\MapHelper;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class PoiCollectionTest
 */
class PoiCollectionTest extends FunctionalTestCase
{
    protected PoiCollection $subject;

    protected ExtConf $extConf;

    protected MapHelper $mapHelper;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);

        $this->mapHelper = new MapHelper($this->extConf);
        GeneralUtility::addInstance(MapHelper::class, $this->mapHelper);

        $this->subject = new PoiCollection();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf,
        );

        parent::tearDown();
    }

    #[Test]
    public function getCollectionTypeInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getCollectionType(),
        );
    }

    #[Test]
    public function setCollectionTypeSetsCollectionType(): void
    {
        $this->subject->setCollectionType('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getCollectionType(),
        );
    }

    #[Test]
    public function getTitleInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTitle(),
        );
    }

    #[Test]
    public function setTitleSetsTitle(): void
    {
        $this->subject->setTitle('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTitle(),
        );
    }

    #[Test]
    public function getAddressInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getAddress(),
        );
    }

    #[Test]
    public function setAddressSetsAddress(): void
    {
        $this->subject->setAddress('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getAddress(),
        );
    }

    #[Test]
    public function getLatitudeInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getLatitude(),
        );
    }

    #[Test]
    public function setLatitudeSetsLatitude(): void
    {
        $this->subject->setLatitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getLatitude(),
        );
    }

    #[Test]
    public function getLongitudeInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getLongitude(),
        );
    }

    #[Test]
    public function setLongitudeSetsLongitude(): void
    {
        $this->subject->setLongitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getLongitude(),
        );
    }

    #[Test]
    public function getRadiusInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getRadius(),
        );
    }

    #[Test]
    public function setRadiusSetsRadius(): void
    {
        $this->subject->setRadius(123456);

        self::assertSame(
            123456,
            $this->subject->getRadius(),
        );
    }

    #[Test]
    public function getPoisInitiallyReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getPois(),
        );
    }

    #[Test]
    public function getStrokeColorInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getStrokeColor(),
        );
    }

    #[Test]
    public function setStrokeColorSetsStrokeColor(): void
    {
        $this->subject->setStrokeColor('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getStrokeColor(),
        );
    }

    #[Test]
    public function getStrokeOpacityInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getStrokeOpacity(),
        );
    }

    #[Test]
    public function setStrokeOpacitySetsStrokeOpacity(): void
    {
        $this->subject->setStrokeOpacity('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getStrokeOpacity(),
        );
    }

    #[Test]
    public function getStrokeWeightInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getStrokeWeight(),
        );
    }

    #[Test]
    public function setStrokeWeightSetsStrokeWeight(): void
    {
        $this->subject->setStrokeWeight('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getStrokeWeight(),
        );
    }

    #[Test]
    public function getFillColorInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getFillColor(),
        );
    }

    #[Test]
    public function setFillColorSetsFillColor(): void
    {
        $this->subject->setFillColor('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFillColor(),
        );
    }

    #[Test]
    public function getFillOpacityInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getFillOpacity(),
        );
    }

    #[Test]
    public function setFillOpacitySetsFillOpacity(): void
    {
        $this->subject->setFillOpacity('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFillOpacity(),
        );
    }

    #[Test]
    public function getInfoWindowContentInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getInfoWindowContent(),
        );
    }

    #[Test]
    public function setInfoWindowContentSetsInfoWindowContent(): void
    {
        $this->subject->setInfoWindowContent('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getInfoWindowContent(),
        );
    }

    #[Test]
    public function getInfoWindowImagesInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getInfoWindowImages(),
        );
    }

    #[Test]
    public function setInfoWindowImagesSetsInfoWindowImages(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setInfoWindowImages($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getInfoWindowImages(),
        );
    }

    #[Test]
    public function addInfoWindowImageAddsOneInfoWindowImage(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setInfoWindowImages($objectStorage);

        $object = new FileReference();
        $this->subject->addInfoWindowImage($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getInfoWindowImages(),
        );
    }

    #[Test]
    public function removeInfoWindowImageRemovesOneInfoWindowImage(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setInfoWindowImages($objectStorage);
        $this->subject->removeInfoWindowImage($object);

        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getInfoWindowImages(),
        );
    }

    #[Test]
    public function getMarkerIconsInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getMarkerIcons(),
        );
    }

    #[Test]
    public function setMarkerIconsSetsMarkerIcons(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setMarkerIcons($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getMarkerIcons(),
        );
    }

    #[Test]
    public function addMarkerIconAddsOneMarkerIcon(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setMarkerIcons($objectStorage);

        $object = new FileReference();

        $this->subject->addMarkerIcon($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getMarkerIcons(),
        );
    }

    #[Test]
    public function removeMarkerIconRemovesOneMarkerIcon(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setMarkerIcons($objectStorage);
        $this->subject->removeMarkerIcon($object);

        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getMarkerIcons(),
        );
    }

    #[Test]
    public function getMarkerIconWidthInitiallyReturnsZero(): void
    {
        self::assertSame(
            25,
            $this->subject->getMarkerIconWidth(),
        );
    }

    #[Test]
    public function setMarkerIconWidthSetsMarkerIconWidth(): void
    {
        $imageMock = $this->createMock(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($imageMock);

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconWidth(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconWidth(),
        );
    }

    #[Test]
    public function setMarkerIconWidthWillGetValueFromCategoryIfEmpty(): void
    {
        $fileReferenceMock = $this->createMock(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReferenceMock);
        $category->setMaps2MarkerIconWidth(123456);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconWidth(),
        );
    }

    #[Test]
    public function setMarkerIconWidthWillGetValueFromCategoryIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconWidth(123456);
        $fileReferenceMock = $this->createMock(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReferenceMock);
        $category->setMaps2MarkerIconWidth(654321);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconWidth(),
        );
    }

    #[Test]
    public function setMarkerIconWidthWillGetValueFromExtConfIfEmpty(): void
    {
        $config = [
            'markerIconWidth' => 123456,
        ];
        $this->extConf = new ExtConf(...$config);
        GeneralUtility::addInstance(ExtConf::class, $this->extConf);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconWidth(),
        );
    }

    #[Test]
    public function setMarkerIconWidthWillGetValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconWidth(123456);

        $config = [
            'markerIconWidth' => 654321,
        ];
        $this->extConf = new ExtConf(...$config);
        GeneralUtility::addInstance(ExtConf::class, $this->extConf);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconWidth(),
        );
    }

    #[Test]
    public function getMarkerIconHeightInitiallyReturnsZero(): void
    {
        self::assertSame(
            40,
            $this->subject->getMarkerIconHeight(),
        );
    }

    #[Test]
    public function setMarkerIconHeightSetsMarkerIconHeight(): void
    {
        $imageMock = $this->createMock(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($imageMock);

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconHeight(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconHeight(),
        );
    }

    #[Test]
    public function setMarkerIconHeightWillGetValueFromCategoryIfEmpty(): void
    {
        $fileReferenceMock = $this->createMock(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReferenceMock);
        $category->setMaps2MarkerIconHeight(123456);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconHeight(),
        );
    }

    #[Test]
    public function setMarkerIconHeightWillGetValueFromCategoryIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconHeight(123456);
        $fileReferenceMock = $this->createMock(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReferenceMock);
        $category->setMaps2MarkerIconHeight(654321);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconHeight(),
        );
    }

    #[Test]
    public function setMarkerIconHeightWillGetValueFromExtConfIfEmpty(): void
    {
        $config = [
            'markerIconHeight' => 123456,
        ];
        $this->extConf = new ExtConf(...$config);
        GeneralUtility::addInstance(ExtConf::class, $this->extConf);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconHeight(),
        );
    }

    #[Test]
    public function getMarkerIconAnchorPosXInitiallyReturnsZero(): void
    {
        self::assertSame(
            13,
            $this->subject->getMarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosXSetsMarkerIconAnchorPosX(): void
    {
        $imageMock = $this->createMock(FileReference::class);

        $images = new ObjectStorage();
        $images->attach($imageMock);

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosX(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosXWillGetValueFromCategoryIfEmpty(): void
    {
        $fileReferenceMock = $this->createMock(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReferenceMock);
        $category->setMaps2MarkerIconAnchorPosX(123456);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosXWillGetValueFromCategoryIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconAnchorPosX(123456);
        $fileReferenceMock = $this->createMock(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReferenceMock);
        $category->setMaps2MarkerIconAnchorPosX(654321);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosXWillGetValueFromExtConfIfEmpty(): void
    {
        $config = [
            'markerIconAnchorPosX' => 123456,
        ];
        $this->extConf = new ExtConf(...$config);
        GeneralUtility::addInstance(ExtConf::class, $this->extConf);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosXWillGetValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconAnchorPosX(123456);

        $config = [
            'markerIconAnchorPosX' => 654321,
        ];
        $this->extConf = new ExtConf(...$config);
        GeneralUtility::addInstance(ExtConf::class, $this->extConf);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function setMarkerIconHeightWillGetValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconHeight(123456);

        $config = [
            'markerIconHeight' => 654321,
        ];
        $this->extConf = new ExtConf(...$config);
        GeneralUtility::addInstance(ExtConf::class, $this->extConf);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconHeight(),
        );
    }

    #[Test]
    public function getMarkerIconAnchorPosYInitiallyReturnsZero(): void
    {
        self::assertSame(
            40,
            $this->subject->getMarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosYSetsMarkerIconAnchorPosY(): void
    {
        $imageMock = $this->createMock(FileReference::class);

        $images = new ObjectStorage();
        $images->attach($imageMock);

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosY(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosYWillGetValueFromCategoryIfEmpty(): void
    {
        $fileReferenceMock = $this->createMock(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReferenceMock);
        $category->setMaps2MarkerIconAnchorPosY(123456);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosYWillGetValueFromCategoryIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconAnchorPosY(123456);
        $fileReference = $this->createMock(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconAnchorPosY(654321);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosYWillGetValueFromExtConfIfEmpty(): void
    {
        $config = [
            'markerIconAnchorPosY' => 123456,
        ];
        $this->extConf = new ExtConf(...$config);
        GeneralUtility::addInstance(ExtConf::class, $this->extConf);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function setMarkerIconAnchorPosYWillGetValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconAnchorPosY(123456);

        $config = [
            'markerIconAnchorPosY' => 654321,
        ];
        $this->extConf = new ExtConf(...$config);
        GeneralUtility::addInstance(ExtConf::class, $this->extConf);

        $subject = new PoiCollection();
        self::assertSame(
            654321,
            $subject->getMarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function getCategoriesInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getCategories(),
        );
    }

    #[Test]
    public function setCategoriesSetsCategories(): void
    {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setCategories($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getCategories(),
        );
    }

    #[Test]
    public function addCategoryAddsOneCategory(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setCategories($objectStorage);

        $object = new Category();

        $this->subject->addCategory($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getCategories(),
        );
    }

    #[Test]
    public function removeCategoryRemovesOneCategory(): void
    {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setCategories($objectStorage);

        $this->subject->removeCategory($object);

        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getCategories(),
        );
    }

    #[Test]
    public function getDistanceInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getDistance(),
        );
    }

    #[Test]
    public function setDistanceSetsDistance(): void
    {
        $this->subject->setDistance(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getDistance(),
        );
    }

    #[Test]
    public function getForeignRecordsInitiallyReturnsArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getForeignRecords(),
        );
    }

    #[Test]
    public function setForeignRecordsSetsForeignRecords(): void
    {
        $this->subject->setForeignRecords(
            [
                [
                    'uid' => 12,
                ],
            ],
        );

        self::assertSame(
            [
                12 => [
                    'uid' => 12,
                ],
            ],
            $this->subject->getForeignRecords(),
        );
    }

    #[Test]
    public function addForeignRecordAddsOneForeignRecord(): void
    {
        $this->subject->addForeignRecord(
            [
                'uid' => 12,
            ],
        );

        self::assertSame(
            [
                12 => [
                    'uid' => 12,
                ],
            ],
            $this->subject->getForeignRecords(),
        );
    }

    #[Test]
    public function removeForeignRecordRemovesOneForeignRecord(): void
    {
        $this->subject->setForeignRecords(
            [
                [
                    'uid' => 12,
                ],
            ],
        );

        $this->subject->removeForeignRecord(
            [
                'uid' => 12,
            ],
        );

        self::assertSame(
            [],
            $this->subject->getForeignRecords(),
        );
    }
}

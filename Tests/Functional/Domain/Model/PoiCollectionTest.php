<?php declare(strict_types=1);

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
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class PoiCollectionTest
 */
class PoiCollectionTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected PoiCollection $subject;

    protected ExtConf $extConf;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->extConf = new ExtConf();

        $this->subject = new PoiCollection();
        $this->subject->injectExtConf($this->extConf);
        $this->subject->injectMapHelper(new MapHelper($this->extConf));
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getCollectionTypeInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getCollectionType()
        );
    }

    /**
     * @test
     */
    public function setCollectionTypeSetsCollectionType(): void
    {
        $this->subject->setCollectionType('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getCollectionType()
        );
    }

    /**
     * @test
     */
    public function getTitleInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleSetsTitle(): void
    {
        $this->subject->setTitle('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getAddressInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressSetsAddress(): void
    {
        $this->subject->setAddress('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function getLatitudeInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getLatitude()
        );
    }

    /**
     * @test
     */
    public function setLatitudeSetsLatitude(): void
    {
        $this->subject->setLatitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getLatitude()
        );
    }

    /**
     * @test
     */
    public function getLongitudeInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getLongitude()
        );
    }

    /**
     * @test
     */
    public function setLongitudeSetsLongitude(): void
    {
        $this->subject->setLongitude(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getLongitude()
        );
    }

    /**
     * @test
     */
    public function getRadiusInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusSetsRadius(): void
    {
        $this->subject->setRadius(123456);

        self::assertSame(
            123456,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function getPoisInitiallyReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getPois()
        );
    }

    /**
     * @test
     */
    public function getStrokeColorInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getStrokeColor()
        );
    }

    /**
     * @test
     */
    public function setStrokeColorSetsStrokeColor(): void
    {
        $this->subject->setStrokeColor('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getStrokeColor()
        );
    }

    /**
     * @test
     */
    public function getStrokeOpacityInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getStrokeOpacity()
        );
    }

    /**
     * @test
     */
    public function setStrokeOpacitySetsStrokeOpacity(): void
    {
        $this->subject->setStrokeOpacity('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getStrokeOpacity()
        );
    }

    /**
     * @test
     */
    public function getStrokeWeightInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function setStrokeWeightSetsStrokeWeight(): void
    {
        $this->subject->setStrokeWeight('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function getFillColorInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getFillColor()
        );
    }

    /**
     * @test
     */
    public function setFillColorSetsFillColor(): void
    {
        $this->subject->setFillColor('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFillColor()
        );
    }

    /**
     * @test
     */
    public function getFillOpacityInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getFillOpacity()
        );
    }

    /**
     * @test
     */
    public function setFillOpacitySetsFillOpacity(): void
    {
        $this->subject->setFillOpacity('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFillOpacity()
        );
    }

    /**
     * @test
     */
    public function getInfoWindowContentInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getInfoWindowContent()
        );
    }

    /**
     * @test
     */
    public function setInfoWindowContentSetsInfoWindowContent(): void
    {
        $this->subject->setInfoWindowContent('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getInfoWindowContent()
        );
    }

    /**
     * @test
     */
    public function getInfoWindowImagesInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getInfoWindowImages()
        );
    }

    /**
     * @test
     */
    public function setInfoWindowImagesSetsInfoWindowImages(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setInfoWindowImages($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getInfoWindowImages()
        );
    }

    /**
     * @test
     */
    public function addInfoWindowImageAddsOneInfoWindowImage(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setInfoWindowImages($objectStorage);

        $object = new FileReference();
        $this->subject->addInfoWindowImage($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getInfoWindowImages()
        );
    }

    /**
     * @test
     */
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
            $this->subject->getInfoWindowImages()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconsInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getMarkerIcons()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconsSetsMarkerIcons(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setMarkerIcons($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getMarkerIcons()
        );
    }

    /**
     * @test
     */
    public function addMarkerIconAddsOneMarkerIcon(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setMarkerIcons($objectStorage);

        $object = new FileReference();

        $this->subject->addMarkerIcon($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getMarkerIcons()
        );
    }

    /**
     * @test
     */
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
            $this->subject->getMarkerIcons()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconWidthInitiallyReturnsZero(): void
    {
        self::assertSame(
            25,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthSetsMarkerIconWidth(): void
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconWidth(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWillGetValueFromCategoryIfEmpty(): void
    {
        $fileReference = $this->prophesize(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconWidth(123456);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWillGetValueFromCategoryIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconWidth(123456);
        $fileReference = $this->prophesize(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconWidth(654321);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWillGetValueFromExtConfIfEmpty(): void
    {
        $this->extConf->setMarkerIconWidth(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWillGetValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconWidth(123456);
        $this->extConf->setMarkerIconWidth(654321);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconHeightInitiallyReturnsZero(): void
    {
        self::assertSame(
            40,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightSetsMarkerIconHeight(): void
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconHeight(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWillGetValueFromCategoryIfEmpty(): void
    {
        $fileReference = $this->prophesize(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconHeight(123456);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWillGetValueFromCategoryIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconHeight(123456);
        $fileReference = $this->prophesize(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconHeight(654321);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWillGetValueFromExtConfIfEmpty(): void
    {
        $this->extConf->setMarkerIconHeight(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconAnchorPosXInitiallyReturnsZero(): void
    {
        self::assertSame(
            13,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXSetsMarkerIconAnchorPosX(): void
    {
        $image = $this->prophesize(FileReference::class);

        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosX(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWillGetValueFromCategoryIfEmpty(): void
    {
        $fileReference = $this->prophesize(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconAnchorPosX(123456);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWillGetValueFromCategoryIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconAnchorPosX(123456);
        $fileReference = $this->prophesize(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconAnchorPosX(654321);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWillGetValueFromExtConfIfEmpty(): void
    {
        $this->extConf->setMarkerIconAnchorPosX(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWillGetValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconAnchorPosX(123456);
        $this->extConf->setMarkerIconAnchorPosX(654321);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWillGetValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconHeight(123456);
        $this->extConf->setMarkerIconHeight(654321);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconAnchorPosYInitiallyReturnsZero(): void
    {
        self::assertSame(
            40,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYSetsMarkerIconAnchorPosY(): void
    {
        $image = $this->prophesize(FileReference::class);

        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosY(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWillGetValueFromCategoryIfEmpty(): void
    {
        $fileReference = $this->prophesize(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconAnchorPosY(123456);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWillGetValueFromCategoryIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconAnchorPosY(123456);
        $fileReference = $this->prophesize(FileReference::class);

        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconAnchorPosY(654321);

        $this->subject->getCategories()->attach($category);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWillGetValueFromExtConfIfEmpty(): void
    {
        $this->extConf->setMarkerIconAnchorPosY(123456);

        self::assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWillGetValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMarkerIconAnchorPosY(123456);
        $this->extConf->setMarkerIconAnchorPosY(654321);

        self::assertSame(
            654321,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function getCategoriesInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function setCategoriesSetsCategories(): void
    {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setCategories($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function addCategoryAddsOneCategory(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setCategories($objectStorage);

        $object = new Category();

        $this->subject->addCategory($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
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
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function getDistanceInitiallyReturnsZero(): void
    {
        self::assertSame(
            0.0,
            $this->subject->getDistance()
        );
    }

    /**
     * @test
     */
    public function setDistanceSetsDistance(): void
    {
        $this->subject->setDistance(1234.56);

        self::assertSame(
            1234.56,
            $this->subject->getDistance()
        );
    }

    /**
     * @test
     */
    public function getForeignRecordsInitiallyReturnsArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getForeignRecords()
        );
    }

    /**
     * @test
     */
    public function setForeignRecordsSetsForeignRecords(): void
    {
        $this->subject->setForeignRecords(
            [
                [
                    'uid' => 12
                ]
            ]
        );

        self::assertSame(
            [
                12 => [
                    'uid' => 12
                ]
            ],
            $this->subject->getForeignRecords()
        );
    }

    /**
     * @test
     */
    public function addForeignRecordAddsOneForeignRecord(): void
    {
        $this->subject->addForeignRecord(
            [
                'uid' => 12
            ]
        );

        self::assertSame(
            [
                12 => [
                    'uid' => 12
                ]
            ],
            $this->subject->getForeignRecords()
        );
    }

    /**
     * @test
     */
    public function removeForeignRecordRemovesOneForeignRecord(): void
    {
        $this->subject->setForeignRecords(
            [
                [
                    'uid' => 12
                ]
            ]
        );

        $this->subject->removeForeignRecord(
            [
                'uid' => 12
            ]
        );

        self::assertSame(
            [],
            $this->subject->getForeignRecords()
        );
    }
}

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
use JWeiland\Maps2\Domain\Model\Poi;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class PoiCollectionTest
 */
class PoiCollectionTest extends FunctionalTestCase
{
    /**
     * @var PoiCollection
     */
    protected $subject;

    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2'
    ];

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->extConf = new ExtConf([]);
        GeneralUtility::setSingletonInstance(ExtConf::class, $this->extConf);
        $this->subject = new PoiCollection();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->subject);
        GeneralUtility::resetSingletonInstances(array());
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getCollectionTypeInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getCollectionType()
        );
    }

    /**
     * @test
     */
    public function setCollectionTypeSetsCollectionType()
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
    public function setCollectionTypeWithIntegerResultsInString()
    {
        $this->subject->setCollectionType(123);
        self::assertSame('123', $this->subject->getCollectionType());
    }

    /**
     * @test
     */
    public function setCollectionTypeWithBooleanResultsInString()
    {
        $this->subject->setCollectionType(true);
        self::assertSame('1', $this->subject->getCollectionType());
    }

    /**
     * @test
     */
    public function getTitleInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
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
    public function setTitleWithIntegerResultsInString()
    {
        $this->subject->setTitle(123);
        self::assertSame('123', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleWithBooleanResultsInString()
    {
        $this->subject->setTitle(true);
        self::assertSame('1', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function getAddressInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressSetsAddress()
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
    public function setAddressWithIntegerResultsInString()
    {
        $this->subject->setAddress(123);
        self::assertSame('123', $this->subject->getAddress());
    }

    /**
     * @test
     */
    public function setAddressWithBooleanResultsInString()
    {
        $this->subject->setAddress(true);
        self::assertSame('1', $this->subject->getAddress());
    }

    /**
     * @test
     */
    public function getLatitudeInitiallyReturnsZero()
    {
        self::assertSame(
            0.0,
            $this->subject->getLatitude()
        );
    }

    /**
     * @test
     */
    public function setLatitudeSetsLatitude()
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
    public function getLongitudeInitiallyReturnsZero()
    {
        self::assertSame(
            0.0,
            $this->subject->getLongitude()
        );
    }

    /**
     * @test
     */
    public function setLongitudeSetsLongitude()
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
    public function getRadiusInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusSetsRadius()
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
    public function setRadiusWithStringResultsInInteger()
    {
        $this->subject->setRadius('123Test');

        self::assertSame(
            123,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusWithBooleanResultsInInteger()
    {
        $this->subject->setRadius(true);

        self::assertSame(
            1,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function getPoisInitiallyReturnsObjectStorage()
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getPois()
        );
    }

    /**
     * @test
     */
    public function setPoisSetsPois()
    {
        $object = new Poi();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setPois($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getPois()
        );
    }

    /**
     * @test
     */
    public function addPoiAddsOnePoi()
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setPois($objectStorage);

        $object = new Poi();
        $this->subject->addPoi($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getPois()
        );
    }

    /**
     * @test
     */
    public function removePoiRemovesOnePoi()
    {
        $object = new Poi();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setPois($objectStorage);

        $this->subject->removePoi($object);
        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getPois()
        );
    }

    /**
     * @test
     */
    public function getStrokeColorInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getStrokeColor()
        );
    }

    /**
     * @test
     */
    public function setStrokeColorSetsStrokeColor()
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
    public function setStrokeColorWithIntegerResultsInString()
    {
        $this->subject->setStrokeColor(123);
        self::assertSame('123', $this->subject->getStrokeColor());
    }

    /**
     * @test
     */
    public function setStrokeColorWithBooleanResultsInString()
    {
        $this->subject->setStrokeColor(true);
        self::assertSame('1', $this->subject->getStrokeColor());
    }

    /**
     * @test
     */
    public function getStrokeOpacityInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getStrokeOpacity()
        );
    }

    /**
     * @test
     */
    public function setStrokeOpacitySetsStrokeOpacity()
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
    public function setStrokeOpacityWithIntegerResultsInString()
    {
        $this->subject->setStrokeOpacity(123);
        self::assertSame('123', $this->subject->getStrokeOpacity());
    }

    /**
     * @test
     */
    public function setStrokeOpacityWithBooleanResultsInString()
    {
        $this->subject->setStrokeOpacity(true);
        self::assertSame('1', $this->subject->getStrokeOpacity());
    }

    /**
     * @test
     */
    public function getStrokeWeightInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function setStrokeWeightSetsStrokeWeight()
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
    public function setStrokeWeightWithIntegerResultsInString()
    {
        $this->subject->setStrokeWeight(123);
        self::assertSame('123', $this->subject->getStrokeWeight());
    }

    /**
     * @test
     */
    public function setStrokeWeightWithBooleanResultsInString()
    {
        $this->subject->setStrokeWeight(true);
        self::assertSame('1', $this->subject->getStrokeWeight());
    }

    /**
     * @test
     */
    public function getFillColorInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getFillColor()
        );
    }

    /**
     * @test
     */
    public function setFillColorSetsFillColor()
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
    public function setFillColorWithIntegerResultsInString()
    {
        $this->subject->setFillColor(123);
        self::assertSame('123', $this->subject->getFillColor());
    }

    /**
     * @test
     */
    public function setFillColorWithBooleanResultsInString()
    {
        $this->subject->setFillColor(true);
        self::assertSame('1', $this->subject->getFillColor());
    }

    /**
     * @test
     */
    public function getFillOpacityInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getFillOpacity()
        );
    }

    /**
     * @test
     */
    public function setFillOpacitySetsFillOpacity()
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
    public function setFillOpacityWithIntegerResultsInString()
    {
        $this->subject->setFillOpacity(123);
        self::assertSame('123', $this->subject->getFillOpacity());
    }

    /**
     * @test
     */
    public function setFillOpacityWithBooleanResultsInString()
    {
        $this->subject->setFillOpacity(true);
        self::assertSame('1', $this->subject->getFillOpacity());
    }

    /**
     * @test
     */
    public function getInfoWindowContentInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getInfoWindowContent()
        );
    }

    /**
     * @test
     */
    public function setInfoWindowContentSetsInfoWindowContent()
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
    public function setInfoWindowContentWithIntegerResultsInString()
    {
        $this->subject->setInfoWindowContent(123);
        self::assertSame('123', $this->subject->getInfoWindowContent());
    }

    /**
     * @test
     */
    public function setInfoWindowContentWithBooleanResultsInString()
    {
        $this->subject->setInfoWindowContent(true);
        self::assertSame('1', $this->subject->getInfoWindowContent());
    }

    /**
     * @test
     */
    public function getInfoWindowImagesInitiallyReturnsObjectStorage()
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getInfoWindowImages()
        );
    }

    /**
     * @test
     */
    public function setInfoWindowImagesSetsInfoWindowImages()
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
    public function addInfoWindowImageAddsOneInfoWindowImage()
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
    public function removeInfoWindowImageRemovesOneInfoWindowImage()
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
    public function getMarkerIconsInitiallyReturnsObjectStorage()
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getMarkerIcons()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconsSetsMarkerIcons()
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
    public function addMarkerIconAddsOneMarkerIcon()
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
    public function removeMarkerIconRemovesOneMarkerIcon()
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
    public function getMarkerIconWidthInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthSetsMarkerIconWidth()
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
    public function setMarkerIconWidthWillGetValueFromCategoryIfEmpty()
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
    public function setMarkerIconWidthWillGetValueFromCategoryIfImageIsEmpty()
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
    public function setMarkerIconWidthWillGetValueFromExtConfIfEmpty()
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
    public function setMarkerIconWidthWillGetValueFromExtConfIfImageIsEmpty()
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
    public function setMarkerIconWidthWithStringResultsInInteger()
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconWidth('123Test');

        self::assertSame(
            123,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWithBooleanResultsInInteger()
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconWidth(true);

        self::assertSame(
            1,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconHeightInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightSetsMarkerIconHeight()
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
    public function setMarkerIconHeightWillGetValueFromCategoryIfEmpty()
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
    public function setMarkerIconHeightWillGetValueFromCategoryIfImageIsEmpty()
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
    public function setMarkerIconHeightWillGetValueFromExtConfIfEmpty()
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
    public function setMarkerIconHeightWithStringResultsInInteger()
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconHeight('123Test');

        self::assertSame(
            123,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWithBooleanResultsInInteger()
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconHeight(true);

        self::assertSame(
            1,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconAnchorPosXInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXSetsMarkerIconAnchorPosX()
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
    public function setMarkerIconAnchorPosXWillGetValueFromCategoryIfEmpty()
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
    public function setMarkerIconAnchorPosXWillGetValueFromCategoryIfImageIsEmpty()
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
    public function setMarkerIconAnchorPosXWillGetValueFromExtConfIfEmpty()
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
    public function setMarkerIconAnchorPosXWillGetValueFromExtConfIfImageIsEmpty()
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
    public function setMarkerIconHeightWillGetValueFromExtConfIfImageIsEmpty()
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
    public function setMarkerIconAnchorPosXWithStringResultsInInteger()
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosX('123Test');

        self::assertSame(
            123,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWithBooleanResultsInInteger()
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosX(true);

        self::assertSame(
            1,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconAnchorPosYInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYSetsMarkerIconAnchorPosY()
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
    public function setMarkerIconAnchorPosYWillGetValueFromCategoryIfEmpty()
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
    public function setMarkerIconAnchorPosYWillGetValueFromCategoryIfImageIsEmpty()
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
    public function setMarkerIconAnchorPosYWillGetValueFromExtConfIfEmpty()
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
    public function setMarkerIconAnchorPosYWillGetValueFromExtConfIfImageIsEmpty()
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
    public function setMarkerIconAnchorPosYWithStringResultsInInteger()
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosY('123Test');

        self::assertSame(
            123,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWithBooleanResultsInInteger()
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosY(true);

        self::assertSame(
            1,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function getCategoriesInitiallyReturnsObjectStorage()
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function setCategoriesSetsCategories()
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
    public function addCategoryAddsOneCategory()
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
    public function removeCategoryRemovesOneCategory()
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
    public function getDistanceInitiallyReturnsZero()
    {
        self::assertSame(
            0.0,
            $this->subject->getDistance()
        );
    }

    /**
     * @test
     */
    public function setDistanceSetsDistance()
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
    public function getForeignRecordsInitiallyReturnsArray()
    {
        self::assertSame(
            [],
            $this->subject->getForeignRecords()
        );
    }

    /**
     * @test
     */
    public function setForeignRecordsSetsForeignRecords()
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
    public function addForeignRecordAddsOneForeignRecord()
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
    public function removeForeignRecordRemovesOneForeignRecord()
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

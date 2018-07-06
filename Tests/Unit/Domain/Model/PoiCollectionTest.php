<?php
namespace JWeiland\Maps2\Tests\Unit\Domain\Model;

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
use JWeiland\Maps2\Domain\Model\Category;
use JWeiland\Maps2\Domain\Model\Poi;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class PoiCollectionTest
 */
class PoiCollectionTest extends UnitTestCase
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
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->extConf = new ExtConf();
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
    public function getCollectionTypeInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getCollectionType()
        );
    }

    /**
     * @test
     */
    public function setCollectionTypeSetsCollectionType() {
        $this->subject->setCollectionType('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getCollectionType()
        );
    }

    /**
     * @test
     */
    public function setCollectionTypeWithIntegerResultsInString() {
        $this->subject->setCollectionType(123);
        $this->assertSame('123', $this->subject->getCollectionType());
    }

    /**
     * @test
     */
    public function setCollectionTypeWithBooleanResultsInString() {
        $this->subject->setCollectionType(true);
        $this->assertSame('1', $this->subject->getCollectionType());
    }

    /**
     * @test
     */
    public function getTitleInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleSetsTitle() {
        $this->subject->setTitle('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleWithIntegerResultsInString() {
        $this->subject->setTitle(123);
        $this->assertSame('123', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleWithBooleanResultsInString() {
        $this->subject->setTitle(true);
        $this->assertSame('1', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function getAddressInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressSetsAddress() {
        $this->subject->setAddress('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressWithIntegerResultsInString() {
        $this->subject->setAddress(123);
        $this->assertSame('123', $this->subject->getAddress());
    }

    /**
     * @test
     */
    public function setAddressWithBooleanResultsInString() {
        $this->subject->setAddress(true);
        $this->assertSame('1', $this->subject->getAddress());
    }

    /**
     * @test
     */
    public function getLatitudeInitiallyReturnsZero() {
        $this->assertSame(
            0.0,
            $this->subject->getLatitude()
        );
    }

    /**
     * @test
     */
    public function setLatitudeSetsLatitude() {
        $this->subject->setLatitude(1234.56);

        $this->assertSame(
            1234.56,
            $this->subject->getLatitude()
        );
    }

    /**
     * @test
     */
    public function getLongitudeInitiallyReturnsZero() {
        $this->assertSame(
            0.0,
            $this->subject->getLongitude()
        );
    }

    /**
     * @test
     */
    public function setLongitudeSetsLongitude() {
        $this->subject->setLongitude(1234.56);

        $this->assertSame(
            1234.56,
            $this->subject->getLongitude()
        );
    }

    /**
     * @test
     */
    public function getRadiusInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusSetsRadius() {
        $this->subject->setRadius(123456);

        $this->assertSame(
            123456,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusWithStringResultsInInteger() {
        $this->subject->setRadius('123Test');

        $this->assertSame(
            123,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function setRadiusWithBooleanResultsInInteger() {
        $this->subject->setRadius(true);

        $this->assertSame(
            1,
            $this->subject->getRadius()
        );
    }

    /**
     * @test
     */
    public function getPoisInitiallyReturnsObjectStorage() {
        $this->assertEquals(
            new ObjectStorage(),
            $this->subject->getPois()
        );
    }

    /**
     * @test
     */
    public function setPoisSetsPois() {
        $object = new Poi();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setPois($objectStorage);

        $this->assertSame(
            $objectStorage,
            $this->subject->getPois()
        );
    }

    /**
     * @test
     */
    public function addPoiAddsOnePoi() {
        $objectStorage = new ObjectStorage();
        $this->subject->setPois($objectStorage);

        $object = new Poi();
        $this->subject->addPoi($object);

        $objectStorage->attach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getPois()
        );
    }

    /**
     * @test
     */
    public function removePoiRemovesOnePoi() {
        $object = new Poi();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setPois($objectStorage);

        $this->subject->removePoi($object);
        $objectStorage->detach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getPois()
        );
    }

    /**
     * @test
     */
    public function getStrokeColorInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getStrokeColor()
        );
    }

    /**
     * @test
     */
    public function setStrokeColorSetsStrokeColor() {
        $this->subject->setStrokeColor('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getStrokeColor()
        );
    }

    /**
     * @test
     */
    public function setStrokeColorWithIntegerResultsInString() {
        $this->subject->setStrokeColor(123);
        $this->assertSame('123', $this->subject->getStrokeColor());
    }

    /**
     * @test
     */
    public function setStrokeColorWithBooleanResultsInString() {
        $this->subject->setStrokeColor(true);
        $this->assertSame('1', $this->subject->getStrokeColor());
    }

    /**
     * @test
     */
    public function getStrokeOpacityInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getStrokeOpacity()
        );
    }

    /**
     * @test
     */
    public function setStrokeOpacitySetsStrokeOpacity() {
        $this->subject->setStrokeOpacity('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getStrokeOpacity()
        );
    }

    /**
     * @test
     */
    public function setStrokeOpacityWithIntegerResultsInString() {
        $this->subject->setStrokeOpacity(123);
        $this->assertSame('123', $this->subject->getStrokeOpacity());
    }

    /**
     * @test
     */
    public function setStrokeOpacityWithBooleanResultsInString() {
        $this->subject->setStrokeOpacity(true);
        $this->assertSame('1', $this->subject->getStrokeOpacity());
    }

    /**
     * @test
     */
    public function getStrokeWeightInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function setStrokeWeightSetsStrokeWeight() {
        $this->subject->setStrokeWeight('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getStrokeWeight()
        );
    }

    /**
     * @test
     */
    public function setStrokeWeightWithIntegerResultsInString() {
        $this->subject->setStrokeWeight(123);
        $this->assertSame('123', $this->subject->getStrokeWeight());
    }

    /**
     * @test
     */
    public function setStrokeWeightWithBooleanResultsInString() {
        $this->subject->setStrokeWeight(true);
        $this->assertSame('1', $this->subject->getStrokeWeight());
    }

    /**
     * @test
     */
    public function getFillColorInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getFillColor()
        );
    }

    /**
     * @test
     */
    public function setFillColorSetsFillColor() {
        $this->subject->setFillColor('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getFillColor()
        );
    }

    /**
     * @test
     */
    public function setFillColorWithIntegerResultsInString() {
        $this->subject->setFillColor(123);
        $this->assertSame('123', $this->subject->getFillColor());
    }

    /**
     * @test
     */
    public function setFillColorWithBooleanResultsInString() {
        $this->subject->setFillColor(true);
        $this->assertSame('1', $this->subject->getFillColor());
    }

    /**
     * @test
     */
    public function getFillOpacityInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getFillOpacity()
        );
    }

    /**
     * @test
     */
    public function setFillOpacitySetsFillOpacity() {
        $this->subject->setFillOpacity('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getFillOpacity()
        );
    }

    /**
     * @test
     */
    public function setFillOpacityWithIntegerResultsInString() {
        $this->subject->setFillOpacity(123);
        $this->assertSame('123', $this->subject->getFillOpacity());
    }

    /**
     * @test
     */
    public function setFillOpacityWithBooleanResultsInString() {
        $this->subject->setFillOpacity(true);
        $this->assertSame('1', $this->subject->getFillOpacity());
    }

    /**
     * @test
     */
    public function getInfoWindowContentInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getInfoWindowContent()
        );
    }

    /**
     * @test
     */
    public function setInfoWindowContentSetsInfoWindowContent() {
        $this->subject->setInfoWindowContent('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getInfoWindowContent()
        );
    }

    /**
     * @test
     */
    public function setInfoWindowContentWithIntegerResultsInString() {
        $this->subject->setInfoWindowContent(123);
        $this->assertSame('123', $this->subject->getInfoWindowContent());
    }

    /**
     * @test
     */
    public function setInfoWindowContentWithBooleanResultsInString() {
        $this->subject->setInfoWindowContent(true);
        $this->assertSame('1', $this->subject->getInfoWindowContent());
    }

    /**
     * @test
     */
    public function getMarkerIconsInitiallyReturnsObjectStorage() {
        $this->assertEquals(
            new ObjectStorage(),
            $this->subject->getMarkerIcons()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconsSetsMarkerIcons() {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setMarkerIcons($objectStorage);

        $this->assertSame(
            $objectStorage,
            $this->subject->getMarkerIcons()
        );
    }

    /**
     * @test
     */
    public function addMarkerIconAddsOneMarkerIcon() {
        $objectStorage = new ObjectStorage();
        $this->subject->setMarkerIcons($objectStorage);

        $object = new FileReference();
        $this->subject->addMarkerIcon($object);

        $objectStorage->attach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getMarkerIcons()
        );
    }

    /**
     * @test
     */
    public function removeMarkerIconRemovesOneMarkerIcon() {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setMarkerIcons($objectStorage);

        $this->subject->removeMarkerIcon($object);
        $objectStorage->detach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getMarkerIcons()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconWidthInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthSetsMarkerIconWidth() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconWidth(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWillGetValueFromCategoryIfEmpty() {
        $fileReference = $this->prophesize(FileReference::class);
        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconWidth(123456);
        $this->subject->getCategories()->attach($category);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWillGetValueFromCategoryIfImageIsEmpty() {
        $this->subject->setMarkerIconWidth(123456);
        $fileReference = $this->prophesize(FileReference::class);
        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconWidth(654321);
        $this->subject->getCategories()->attach($category);

        $this->assertSame(
            654321,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWillGetValueFromExtConfIfEmpty() {
        $this->extConf->setMarkerIconWidth(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWillGetValueFromExtConfIfImageIsEmpty() {
        $this->subject->setMarkerIconWidth(123456);
        $this->extConf->setMarkerIconWidth(654321);

        $this->assertSame(
            654321,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWithStringResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconWidth('123Test');

        $this->assertSame(
            123,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconWidthWithBooleanResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconWidth(true);

        $this->assertSame(
            1,
            $this->subject->getMarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconHeightInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightSetsMarkerIconHeight() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconHeight(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWillGetValueFromCategoryIfEmpty() {
        $fileReference = $this->prophesize(FileReference::class);
        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconHeight(123456);
        $this->subject->getCategories()->attach($category);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWillGetValueFromCategoryIfImageIsEmpty() {
        $this->subject->setMarkerIconHeight(123456);
        $fileReference = $this->prophesize(FileReference::class);
        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconHeight(654321);
        $this->subject->getCategories()->attach($category);

        $this->assertSame(
            654321,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWillGetValueFromExtConfIfEmpty() {
        $this->extConf->setMarkerIconHeight(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWithStringResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconHeight('123Test');

        $this->assertSame(
            123,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWithBooleanResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconHeight(true);

        $this->assertSame(
            1,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconAnchorPosXInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXSetsMarkerIconAnchorPosX() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosX(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWillGetValueFromCategoryIfEmpty() {
        $fileReference = $this->prophesize(FileReference::class);
        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconAnchorPosX(123456);
        $this->subject->getCategories()->attach($category);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWillGetValueFromCategoryIfImageIsEmpty() {
        $this->subject->setMarkerIconAnchorPosX(123456);
        $fileReference = $this->prophesize(FileReference::class);
        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconAnchorPosX(654321);
        $this->subject->getCategories()->attach($category);

        $this->assertSame(
            654321,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWillGetValueFromExtConfIfEmpty() {
        $this->extConf->setMarkerIconAnchorPosX(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWillGetValueFromExtConfIfImageIsEmpty() {
        $this->subject->setMarkerIconAnchorPosX(123456);
        $this->extConf->setMarkerIconAnchorPosX(654321);

        $this->assertSame(
            654321,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconHeightWillGetValueFromExtConfIfImageIsEmpty() {
        $this->subject->setMarkerIconHeight(123456);
        $this->extConf->setMarkerIconHeight(654321);

        $this->assertSame(
            654321,
            $this->subject->getMarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWithStringResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosX('123Test');

        $this->assertSame(
            123,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosXWithBooleanResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosX(true);

        $this->assertSame(
            1,
            $this->subject->getMarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function getMarkerIconAnchorPosYInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYSetsMarkerIconAnchorPosY() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosY(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWillGetValueFromCategoryIfEmpty() {
        $fileReference = $this->prophesize(FileReference::class);
        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconAnchorPosY(123456);
        $this->subject->getCategories()->attach($category);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWillGetValueFromCategoryIfImageIsEmpty() {
        $this->subject->setMarkerIconAnchorPosY(123456);
        $fileReference = $this->prophesize(FileReference::class);
        $category = new Category();
        $category->getMaps2MarkerIcons()->attach($fileReference);
        $category->setMaps2MarkerIconAnchorPosY(654321);
        $this->subject->getCategories()->attach($category);

        $this->assertSame(
            654321,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWillGetValueFromExtConfIfEmpty() {
        $this->extConf->setMarkerIconAnchorPosY(123456);

        $this->assertSame(
            123456,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWillGetValueFromExtConfIfImageIsEmpty() {
        $this->subject->setMarkerIconAnchorPosY(123456);
        $this->extConf->setMarkerIconAnchorPosY(654321);

        $this->assertSame(
            654321,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWithStringResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosY('123Test');

        $this->assertSame(
            123,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMarkerIconAnchorPosYWithBooleanResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMarkerIcons($images);
        $this->subject->setMarkerIconAnchorPosY(true);

        $this->assertSame(
            1,
            $this->subject->getMarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function getCategoriesInitiallyReturnsObjectStorage() {
        $this->assertEquals(
            new ObjectStorage(),
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function setCategoriesSetsCategories() {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setCategories($objectStorage);

        $this->assertSame(
            $objectStorage,
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function addCategoryAddsOneCategory() {
        $objectStorage = new ObjectStorage();
        $this->subject->setCategories($objectStorage);

        $object = new Category();
        $this->subject->addCategory($object);

        $objectStorage->attach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function removeCategoryRemovesOneCategory() {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setCategories($objectStorage);

        $this->subject->removeCategory($object);
        $objectStorage->detach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function getDistanceInitiallyReturnsZero() {
        $this->assertSame(
            0.0,
            $this->subject->getDistance()
        );
    }

    /**
     * @test
     */
    public function setDistanceSetsDistance() {
        $this->subject->setDistance(1234.56);

        $this->assertSame(
            1234.56,
            $this->subject->getDistance()
        );
    }
}

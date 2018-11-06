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
use JWeiland\Maps2\Domain\Model\PoiCollection;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class CategoryTest
 */
class CategoryTest extends UnitTestCase
{
    /**
     * @var Category
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
        $this->subject = new Category();
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
    public function getMaps2MarkerIconsInitiallyReturnsObjectStorage() {
        $this->assertEquals(
            new ObjectStorage(),
            $this->subject->getMaps2MarkerIcons()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconsSetsMaps2MarkerIcons() {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setMaps2MarkerIcons($objectStorage);

        $this->assertSame(
            $objectStorage,
            $this->subject->getMaps2MarkerIcons()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWithEmptyStorageWillReturnEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getMaps2MarkerIcon()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWithMissingCoreFileReferenceWillReturnEmptyString() {
        /** @var FileReference $fileReference */
        $fileReference = $this->prophesize(FileReference::class);
        $fileReference->getOriginalResource()->shouldBeCalled()->willReturn(null);
        $this->subject->getMaps2MarkerIcons()->attach($fileReference->reveal());
        $this->assertSame(
            '',
            $this->subject->getMaps2MarkerIcon()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWithWrongObjectInStorageWillReturnEmptyString() {
        $this->subject->getMaps2MarkerIcons()->attach(new PoiCollection());
        $this->assertSame(
            '',
            $this->subject->getMaps2MarkerIcon()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWillReturnIconPath() {
        $file = $this->prophesize(File::class);
        $file->getUid()->shouldBeCalled()->willReturn(123);
        /** @var \TYPO3\CMS\Core\Resource\FileReference $coreFileReference */
        $coreFileReference = $this->prophesize(\TYPO3\CMS\Core\Resource\FileReference::class);
        $coreFileReference->getOriginalFile()->shouldBeCalled()->willReturn($file->reveal());
        $coreFileReference->getPublicUrl(false)->shouldBeCalled()->willReturn('ImagePath');
        $fileReference = new FileReference();
        $fileReference->setOriginalResource($coreFileReference->reveal());
        $this->subject->getMaps2MarkerIcons()->attach($fileReference);
        $this->assertSame(
            'ImagePath',
            $this->subject->getMaps2MarkerIcon()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWidthInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMaps2MarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWidthReturnsValueFromExtConfIfEmpty() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->extConf->setMarkerIconWidth(123);
        $this->assertSame(
            123,
            $this->subject->getMaps2MarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWidthReturnsValueFromExtConfIfImageIsEmpty() {
        $this->subject->setMaps2MarkerIconWidth(123);
        $this->extConf->setMarkerIconWidth(321);
        $this->assertSame(
            321,
            $this->subject->getMaps2MarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconWidthSetsMaps2MarkerIconWidth() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconWidth(123456);
        $this->assertSame(
            123456,
            $this->subject->getMaps2MarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconWidthWithStringResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconWidth('123Test');
        $this->assertSame(
            123,
            $this->subject->getMaps2MarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconWidthWithBooleanResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconWidth(true);
        $this->assertSame(
            1,
            $this->subject->getMaps2MarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconHeightInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMaps2MarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconHeightReturnsValueFromExtConfIfEmpty() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->extConf->setMarkerIconHeight(123);
        $this->assertSame(
            123,
            $this->subject->getMaps2MarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconHeightReturnsValueFromExtConfIfImageIsEmpty() {
        $this->subject->setMaps2MarkerIconHeight(123);
        $this->extConf->setMarkerIconHeight(321);
        $this->assertSame(
            321,
            $this->subject->getMaps2MarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconHeightSetsMaps2MarkerIconHeight() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconHeight(123456);
        $this->assertSame(
            123456,
            $this->subject->getMaps2MarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconHeightWithStringResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconHeight('123Test');
        $this->assertSame(
            123,
            $this->subject->getMaps2MarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconHeightWithBooleanResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconHeight(true);
        $this->assertSame(
            1,
            $this->subject->getMaps2MarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosXInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMaps2MarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosXReturnsValueFromExtConfIfEmpty() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->extConf->setMarkerIconAnchorPosX(123);
        $this->assertSame(
            123,
            $this->subject->getMaps2MarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosXReturnsValueFromExtConfIfImageIsEmpty() {
        $this->subject->setMaps2MarkerIconAnchorPosX(123);
        $this->extConf->setMarkerIconAnchorPosX(321);
        $this->assertSame(
            321,
            $this->subject->getMaps2MarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconAnchorPosXSetsMaps2MarkerIconAnchorPosX() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconAnchorPosX(123456);
        $this->assertSame(
            123456,
            $this->subject->getMaps2MarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconAnchorPosXWithStringResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconAnchorPosX('123Test');
        $this->assertSame(
            123,
            $this->subject->getMaps2MarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconAnchorPosXWithBooleanResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconAnchorPosX(true);
        $this->assertSame(
            1,
            $this->subject->getMaps2MarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosYInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getMaps2MarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosYReturnsValueFromExtConfIfEmpty() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->extConf->setMarkerIconAnchorPosY(123);
        $this->assertSame(
            123,
            $this->subject->getMaps2MarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosYWidthReturnsValueFromExtConfIfImageIsEmpty() {
        $this->subject->setMaps2MarkerIconAnchorPosY(123);
        $this->extConf->setMarkerIconAnchorPosY(321);
        $this->assertSame(
            321,
            $this->subject->getMaps2MarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconAnchorPosYSetsMaps2MarkerIconAnchorPosY() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconAnchorPosY(123456);
        $this->assertSame(
            123456,
            $this->subject->getMaps2MarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconAnchorPosYWithStringResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconAnchorPosY('123Test');
        $this->assertSame(
            123,
            $this->subject->getMaps2MarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconAnchorPosYWithBooleanResultsInInteger() {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconAnchorPosY(true);
        $this->assertSame(
            1,
            $this->subject->getMaps2MarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function getSortingInitiallyReturnsZero() {
        $this->assertSame(
            0,
            $this->subject->getSorting()
        );
    }

    /**
     * @test
     */
    public function setSortingSetsSorting() {
        $this->subject->setSorting(123456);

        $this->assertSame(
            123456,
            $this->subject->getSorting()
        );
    }

    /**
     * @test
     */
    public function setSortingWithStringResultsInInteger() {
        $this->subject->setSorting('123Test');

        $this->assertSame(
            123,
            $this->subject->getSorting()
        );
    }

    /**
     * @test
     */
    public function setSortingWithBooleanResultsInInteger() {
        $this->subject->setSorting(true);

        $this->assertSame(
            1,
            $this->subject->getSorting()
        );
    }
}

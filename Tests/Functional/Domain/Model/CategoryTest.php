<?php

declare(strict_types=1);

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
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class CategoryTest
 */
class CategoryTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected Category $subject;

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
        GeneralUtility::setSingletonInstance(ExtConf::class, $this->extConf);

        $this->subject = new Category();
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
    public function getMaps2MarkerIconsInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getMaps2MarkerIcons()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconsSetsMaps2MarkerIcons(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setMaps2MarkerIcons($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getMaps2MarkerIcons()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWithEmptyStorageWillReturnEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getMaps2MarkerIcon()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWithMissingCoreFileReferenceWillReturnEmptyString(): void
    {
        /** @var FileReference $fileReference */
        $fileReference = $this->prophesize(FileReference::class);
        $fileReference->getOriginalResource()->shouldBeCalled()->willReturn(null);
        $this->subject->getMaps2MarkerIcons()->attach($fileReference->reveal());
        self::assertSame(
            '',
            $this->subject->getMaps2MarkerIcon()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWithWrongObjectInStorageWillReturnEmptyString(): void
    {
        $this->subject->getMaps2MarkerIcons()->attach(new PoiCollection());
        self::assertSame(
            '',
            $this->subject->getMaps2MarkerIcon()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWillReturnIconPath(): void
    {
        $file = $this->prophesize(File::class);
        $file
            ->getUid()
            ->shouldBeCalled()
            ->willReturn(123);

        /** @var \TYPO3\CMS\Core\Resource\FileReference $coreFileReference */
        $coreFileReference = $this->prophesize(\TYPO3\CMS\Core\Resource\FileReference::class);
        $coreFileReference->getOriginalFile()->shouldBeCalled()->willReturn($file->reveal());
        $coreFileReference
            ->getPublicUrl(false)
            ->shouldBeCalled()
            ->willReturn('ImagePath');

        $fileReference = new FileReference();
        $fileReference->setOriginalResource($coreFileReference->reveal());
        $this->subject->getMaps2MarkerIcons()->attach($fileReference);

        self::assertStringEndsWith(
            'ImagePath',
            $this->subject->getMaps2MarkerIcon()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWidthInitiallyReturns25(): void
    {
        self::assertSame(
            25,
            $this->subject->getMaps2MarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWidthReturnsValueFromExtConfIfEmpty(): void
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->extConf->setMarkerIconWidth(123);

        self::assertSame(
            123,
            $this->subject->getMaps2MarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconWidthReturnsValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMaps2MarkerIconWidth(123);
        $this->extConf->setMarkerIconWidth(321);
        self::assertSame(
            321,
            $this->subject->getMaps2MarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconWidthSetsMaps2MarkerIconWidth(): void
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconWidth(123456);
        self::assertSame(
            123456,
            $this->subject->getMaps2MarkerIconWidth()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconHeightInitiallyReturnsZero(): void
    {
        self::assertSame(
            40,
            $this->subject->getMaps2MarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconHeightReturnsValueFromExtConfIfEmpty(): void
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->extConf->setMarkerIconHeight(123);
        self::assertSame(
            123,
            $this->subject->getMaps2MarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconHeightReturnsValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMaps2MarkerIconHeight(123);
        $this->extConf->setMarkerIconHeight(321);
        self::assertSame(
            321,
            $this->subject->getMaps2MarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconHeightSetsMaps2MarkerIconHeight(): void
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconHeight(123456);
        self::assertSame(
            123456,
            $this->subject->getMaps2MarkerIconHeight()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosXInitiallyReturnsZero(): void
    {
        self::assertSame(
            13,
            $this->subject->getMaps2MarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosXReturnsValueFromExtConfIfEmpty(): void
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->extConf->setMarkerIconAnchorPosX(123);
        self::assertSame(
            123,
            $this->subject->getMaps2MarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosXReturnsValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMaps2MarkerIconAnchorPosX(123);
        $this->extConf->setMarkerIconAnchorPosX(321);
        self::assertSame(
            321,
            $this->subject->getMaps2MarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconAnchorPosXSetsMaps2MarkerIconAnchorPosX(): void
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconAnchorPosX(123456);
        self::assertSame(
            123456,
            $this->subject->getMaps2MarkerIconAnchorPosX()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosYInitiallyReturnsZero(): void
    {
        self::assertSame(
            40,
            $this->subject->getMaps2MarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosYReturnsValueFromExtConfIfEmpty(): void
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->extConf->setMarkerIconAnchorPosY(123);
        self::assertSame(
            123,
            $this->subject->getMaps2MarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function getMaps2MarkerIconAnchorPosYWidthReturnsValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMaps2MarkerIconAnchorPosY(123);
        $this->extConf->setMarkerIconAnchorPosY(321);
        self::assertSame(
            321,
            $this->subject->getMaps2MarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function setMaps2MarkerIconAnchorPosYSetsMaps2MarkerIconAnchorPosY(): void
    {
        $image = $this->prophesize(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($image->reveal());

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconAnchorPosY(123456);
        self::assertSame(
            123456,
            $this->subject->getMaps2MarkerIconAnchorPosY()
        );
    }

    /**
     * @test
     */
    public function getSortingInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getSorting()
        );
    }

    /**
     * @test
     */
    public function setSortingSetsSorting(): void
    {
        $this->subject->setSorting(123456);

        self::assertSame(
            123456,
            $this->subject->getSorting()
        );
    }
}

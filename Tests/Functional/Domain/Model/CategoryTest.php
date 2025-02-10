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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class CategoryTest
 */
class CategoryTest extends FunctionalTestCase
{
    protected Category $subject;

    protected ExtConf $extConf;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);

        $this->subject = new Category();
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
    public function getMaps2MarkerIconsInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getMaps2MarkerIcons(),
        );
    }

    #[Test]
    public function setMaps2MarkerIconsSetsMaps2MarkerIcons(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setMaps2MarkerIcons($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getMaps2MarkerIcons(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconWithEmptyStorageWillReturnEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getMaps2MarkerIcon(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconWithMissingCoreFileReferenceWillReturnEmptyString(): void
    {
        /** @var FileReference|MockObject $fileReferenceMock */
        $fileReferenceMock = $this->createMock(FileReference::class);
        $fileReferenceMock
            ->expects(self::atLeastOnce())
            ->method('getOriginalResource')
            ->willReturn($this->createMock(FileReference::class));

        $this->subject->getMaps2MarkerIcons()->attach($fileReferenceMock);

        self::assertSame(
            '',
            $this->subject->getMaps2MarkerIcon(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconWithWrongObjectInStorageWillReturnEmptyString(): void
    {
        $this->subject->getMaps2MarkerIcons()->attach(new PoiCollection());

        self::assertSame(
            '',
            $this->subject->getMaps2MarkerIcon(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconWillReturnIconPath(): void
    {
        $fileMock = $this->createMock(File::class);
        $fileMock
            ->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(123);

        /** @var \TYPO3\CMS\Core\Resource\FileReference|MockObject $coreFileReferenceMock */
        $coreFileReferenceMock = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $coreFileReferenceMock
            ->expects(self::atLeastOnce())
            ->method('getOriginalFile')
            ->willReturn($fileMock);
        $coreFileReferenceMock
            ->expects(self::atLeastOnce())
            ->method('getPublicUrl')
            ->willReturn('ImagePath');

        $fileReference = new FileReference();
        $fileReference->setOriginalResource($coreFileReferenceMock);
        $this->subject->getMaps2MarkerIcons()->attach($fileReference);

        self::assertStringEndsWith(
            'ImagePath',
            $this->subject->getMaps2MarkerIcon(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconWidthInitiallyReturns25(): void
    {
        self::assertSame(
            25,
            $this->subject->getMaps2MarkerIconWidth(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconWidthReturnsValueFromExtConfIfEmpty(): void
    {
        $imageMock = $this->createMock(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($imageMock);

        $this->subject->setMaps2MarkerIcons($images);

        $config = [
            'markerIconWidth' => 123
        ];
        $this->extConf = new ExtConf(...$config);

        self::assertSame(
            123,
            $this->subject->getMaps2MarkerIconWidth(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconWidthReturnsValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMaps2MarkerIconWidth(123);

        $config = [
            'markerIconWidth' => 321
        ];
        $this->extConf = new ExtConf(...$config);

        self::assertSame(
            321,
            $this->subject->getMaps2MarkerIconWidth(),
        );
    }

    #[Test]
    public function setMaps2MarkerIconWidthSetsMaps2MarkerIconWidth(): void
    {
        $imageMock = $this->createMock(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($imageMock);

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconWidth(123456);
        self::assertSame(
            123456,
            $this->subject->getMaps2MarkerIconWidth(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconHeightInitiallyReturnsZero(): void
    {
        self::assertSame(
            40,
            $this->subject->getMaps2MarkerIconHeight(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconHeightReturnsValueFromExtConfIfEmpty(): void
    {
        $imageMock = $this->createMock(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($imageMock);

        $this->subject->setMaps2MarkerIcons($images);

        $config = [
            'markerIconHeight' => 123
        ];
        $this->extConf = new ExtConf(...$config);
        self::assertSame(
            123,
            $this->subject->getMaps2MarkerIconHeight(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconHeightReturnsValueFromExtConfIfImageIsEmpty(): void
    {
        $this->subject->setMaps2MarkerIconHeight(123);

        $config = [
            'markerIconHeight' => 321
        ];
        $this->extConf = new ExtConf(...$config);
        self::assertSame(
            321,
            $this->subject->getMaps2MarkerIconHeight(),
        );
    }

    #[Test]
    public function setMaps2MarkerIconHeightSetsMaps2MarkerIconHeight(): void
    {
        $imageMock = $this->createMock(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($imageMock);

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconHeight(123456);
        self::assertSame(
            123456,
            $this->subject->getMaps2MarkerIconHeight(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconAnchorPosXInitiallyReturnsZero(): void
    {
        self::assertSame(
            13,
            $this->subject->getMaps2MarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconAnchorPosXReturnsValueFromExtConfIfEmpty(): void
    {
        $imageMock = $this->createMock(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($imageMock);

        $this->subject->setMaps2MarkerIcons($images);

        $config = [
            'markerIconAnchorPosX' => 123
        ];
        $this->extConf = new ExtConf(...$config);

        self::assertSame(
            123,
            $this->subject->getMaps2MarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconAnchorPosXReturnsValueFromExtConfIfImageIsEmpty(): void
    {
        $config = [
            'markerIconAnchorPosX' => 321
        ];
        $this->extConf = new ExtConf(...$config);
        $this->subject->setMaps2MarkerIconAnchorPosX(123);
        self::assertSame(
            321,
            $this->subject->getMaps2MarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function setMaps2MarkerIconAnchorPosXSetsMaps2MarkerIconAnchorPosX(): void
    {
        $imageMock = $this->createMock(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($imageMock);

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconAnchorPosX(123456);
        self::assertSame(
            123456,
            $this->subject->getMaps2MarkerIconAnchorPosX(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconAnchorPosYInitiallyReturnsZero(): void
    {
        self::assertSame(
            40,
            $this->subject->getMaps2MarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconAnchorPosYReturnsValueFromExtConfIfEmpty(): void
    {
        $imageMock = $this->createMock(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($imageMock);

        $config = [
            'markerIconAnchorPosY' => 123
        ];
        $this->extConf = new ExtConf(...$config);

        $this->subject->setMaps2MarkerIcons($images);
        self::assertSame(
            123,
            $this->subject->getMaps2MarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function getMaps2MarkerIconAnchorPosYWidthReturnsValueFromExtConfIfImageIsEmpty(): void
    {
        $config = [
            'markerIconAnchorPosY' => 321
        ];
        $this->extConf = new ExtConf(...$config);
        $this->subject->setMaps2MarkerIconAnchorPosY(123);
        self::assertSame(
            321,
            $this->subject->getMaps2MarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function setMaps2MarkerIconAnchorPosYSetsMaps2MarkerIconAnchorPosY(): void
    {
        $imageMock = $this->createMock(FileReference::class);
        $images = new ObjectStorage();
        $images->attach($imageMock);

        $this->subject->setMaps2MarkerIcons($images);
        $this->subject->setMaps2MarkerIconAnchorPosY(123456);
        self::assertSame(
            123456,
            $this->subject->getMaps2MarkerIconAnchorPosY(),
        );
    }

    #[Test]
    public function getSortingInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getSorting(),
        );
    }

    #[Test]
    public function setSortingSetsSorting(): void
    {
        $this->subject->setSorting(123456);

        self::assertSame(
            123456,
            $this->subject->getSorting(),
        );
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Domain\Model;

use JWeiland\Maps2\Domain\Traits\GetExtConfTrait;
use JWeiland\Maps2\Domain\Traits\GetWebPathOfFileReferenceTrait;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Domain Model for: sys_category
 */
class Category extends \TYPO3\CMS\Extbase\Domain\Model\Category
{
    use GetExtConfTrait;
    use GetWebPathOfFileReferenceTrait;

    /**
     * @var ObjectStorage<FileReference>
     */
    protected ?ObjectStorage $maps2MarkerIcons = null;

    protected int $maps2MarkerIconWidth = 0;

    protected int $maps2MarkerIconHeight = 0;

    protected int $maps2MarkerIconAnchorPosX = 0;

    protected int $maps2MarkerIconAnchorPosY = 0;

    protected int $sorting = 0;

    public function __construct()
    {
        $this->maps2MarkerIcons = new ObjectStorage();
    }

    /**
     * Called again with initialize object, as fetching an entity from the DB does not use the constructor
     */
    public function initializeObject(): void
    {
        $this->maps2MarkerIcons = $this->maps2MarkerIcons ?? new ObjectStorage();
    }

    public function getMaps2MarkerIcons(): ObjectStorage
    {
        return $this->maps2MarkerIcons;
    }

    public function getMaps2MarkerIcon(): string
    {
        $this->maps2MarkerIcons->rewind();

        return $this->getWebPathOfFileReference($this->maps2MarkerIcons->current());
    }

    public function setMaps2MarkerIcons(ObjectStorage $maps2MarkerIcons): void
    {
        $this->maps2MarkerIcons = $maps2MarkerIcons;
    }

    public function getMaps2MarkerIconWidth(): int
    {
        $markerIconWidth = $this->getExtConf()->getMarkerIconWidth();

        // Only use icon width of this model, if model has marker icons
        if ($this->maps2MarkerIconWidth > 0 && $this->getMaps2MarkerIcons()->count() !== 0) {
            $markerIconWidth = $this->maps2MarkerIconWidth;
        }

        return $markerIconWidth;
    }

    public function setMaps2MarkerIconWidth(int $maps2MarkerIconWidth): void
    {
        $this->maps2MarkerIconWidth = $maps2MarkerIconWidth;
    }

    public function getMaps2MarkerIconHeight(): int
    {
        $markerIconHeight = $this->getExtConf()->getMarkerIconHeight();

        // Only use icon height of this model, if model has marker icons
        if ($this->maps2MarkerIconHeight > 0 && $this->getMaps2MarkerIcons()->count() !== 0) {
            $markerIconHeight = $this->maps2MarkerIconHeight;
        }

        return $markerIconHeight;
    }

    public function setMaps2MarkerIconHeight(int $maps2MarkerIconHeight): void
    {
        $this->maps2MarkerIconHeight = $maps2MarkerIconHeight;
    }

    public function getMaps2MarkerIconAnchorPosX(): int
    {
        $markerIconAnchorPosX = $this->getExtConf()->getMarkerIconAnchorPosX();

        // Only use icon anchor pos X of this model, if model has marker icons
        if ($this->maps2MarkerIconAnchorPosX > 0 && $this->getMaps2MarkerIcons()->count() !== 0) {
            $markerIconAnchorPosX = $this->maps2MarkerIconAnchorPosX;
        }

        return $markerIconAnchorPosX;
    }

    public function setMaps2MarkerIconAnchorPosX(int $maps2MarkerIconAnchorPosX): void
    {
        $this->maps2MarkerIconAnchorPosX = $maps2MarkerIconAnchorPosX;
    }

    public function getMaps2MarkerIconAnchorPosY(): int
    {
        $markerIconAnchorPosY = $this->getExtConf()->getMarkerIconAnchorPosY();

        // Only use icon anchor pos Y of this model, if model has marker icons
        if ($this->maps2MarkerIconAnchorPosY > 0 && $this->getMaps2MarkerIcons()->count() !== 0) {
            $markerIconAnchorPosY = $this->maps2MarkerIconAnchorPosY;
        }

        return $markerIconAnchorPosY;
    }

    public function setMaps2MarkerIconAnchorPosY(int $maps2MarkerIconAnchorPosY): void
    {
        $this->maps2MarkerIconAnchorPosY = $maps2MarkerIconAnchorPosY;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }
}

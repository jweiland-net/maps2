<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Domain\Model;

use JWeiland\Maps2\Configuration\ExtConf;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Domain Model for: sys_category
 */
class Category extends \TYPO3\CMS\Extbase\Domain\Model\Category
{
    protected ExtConf $extConf;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
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
     * As constructor arguments will be removed while instantiation of domain models,
     * we have to add extConf with help of an inject method
     */
    public function injectExtConf(ExtConf $extConf): void
    {
        $this->extConf = $extConf;
    }

    public function getMaps2MarkerIcons(): ObjectStorage
    {
        return $this->maps2MarkerIcons;
    }

    public function getMaps2MarkerIcon(): string
    {
        if ($this->maps2MarkerIcons->count() === 0) {
            return '';
        }

        $this->maps2MarkerIcons->rewind();
        // only one icon is allowed, so current() will give us the first icon
        $iconReference = $this->maps2MarkerIcons->current();
        if (!$iconReference instanceof FileReference) {
            return '';
        }

        $falIconReference = $iconReference->getOriginalResource();
        if (!$falIconReference instanceof \TYPO3\CMS\Core\Resource\FileReference) {
            return '';
        }

        $siteUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');

        // Argument deprecated with TYPO3 11.3. Remove while removing TYPO3 10 compatibility
        return $siteUrl . $falIconReference->getPublicUrl(false);
    }

    public function setMaps2MarkerIcons(ObjectStorage $maps2MarkerIcons): void
    {
        $this->maps2MarkerIcons = $maps2MarkerIcons;
    }

    public function getMaps2MarkerIconWidth(): int
    {
        // prevent using local maps2MarkerIconWidth, if no markerIcon is set.
        if ($this->maps2MarkerIconWidth === 0 || $this->getMaps2MarkerIcons()->count() === 0) {
            return $this->extConf->getMarkerIconWidth();
        }

        return $this->maps2MarkerIconWidth;
    }

    public function setMaps2MarkerIconWidth(int $maps2MarkerIconWidth): void
    {
        $this->maps2MarkerIconWidth = $maps2MarkerIconWidth;
    }

    public function getMaps2MarkerIconHeight(): int
    {
        // prevent using local maps2MarkerIconHeight, if no markerIcon is set.
        if ($this->maps2MarkerIconHeight === 0 || $this->getMaps2MarkerIcons()->count() === 0) {
            return $this->extConf->getMarkerIconHeight();
        }

        return $this->maps2MarkerIconHeight;
    }

    public function setMaps2MarkerIconHeight(int $maps2MarkerIconHeight): void
    {
        $this->maps2MarkerIconHeight = $maps2MarkerIconHeight;
    }

    public function getMaps2MarkerIconAnchorPosX(): int
    {
        // prevent using local maps2MarkerIconAnchorPosX, if no markerIcon is set.
        if ($this->maps2MarkerIconAnchorPosX === 0 || $this->getMaps2MarkerIcons()->count() === 0) {
            return $this->extConf->getMarkerIconAnchorPosX();
        }

        return $this->maps2MarkerIconAnchorPosX;
    }

    public function setMaps2MarkerIconAnchorPosX(int $maps2MarkerIconAnchorPosX): void
    {
        $this->maps2MarkerIconAnchorPosX = $maps2MarkerIconAnchorPosX;
    }

    public function getMaps2MarkerIconAnchorPosY(): int
    {
        // prevent using local maps2MarkerIconAnchorPosY, if no markerIcon is set.
        if ($this->maps2MarkerIconAnchorPosY === 0 || $this->getMaps2MarkerIcons()->count() === 0) {
            return $this->extConf->getMarkerIconAnchorPosY();
        }

        return $this->maps2MarkerIconAnchorPosY;
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

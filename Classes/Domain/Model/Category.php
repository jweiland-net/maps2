<?php
namespace JWeiland\Maps2\Domain\Model;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Domain Model for: sys_category
 */
class Category extends \TYPO3\CMS\Extbase\Domain\Model\Category
{
    /**
     * markerIcon
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $maps2MarkerIcons;

    /**
     * markerIconWidth
     *
     * @var int
     */
    protected $maps2MarkerIconWidth = 0;

    /**
     * markerIconHeight
     *
     * @var int
     */
    protected $maps2MarkerIconHeight = 0;

    /**
     * markerIconAnchorPosX
     *
     * @var int
     */
    protected $maps2MarkerIconAnchorPosX = 0;

    /**
     * markerIconAnchorPosY
     *
     * @var int
     */
    protected $maps2MarkerIconAnchorPosY = 0;

    /**
     * Sorting
     *
     * @var int
     */
    protected $sorting = 0;

    /**
     * Constructor of this model class
     */
    public function __construct()
    {
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties.
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->maps2MarkerIcons = new ObjectStorage();
    }

    /**
     * Returns the maps2MarkerIcons
     *
     * @return ObjectStorage $maps2MarkerIcons
     */
    public function getMaps2MarkerIcons()
    {
        return $this->maps2MarkerIcons;
    }

    /**
     * Returns the maps2MarkerIcon
     *
     * @return string Absolute path to marker icon
     */
    public function getMaps2MarkerIcon()
    {
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

        return $falIconReference->getPublicUrl(false);
    }

    /**
     * Sets the maps2MarkerIcons
     *
     * @param ObjectStorage $maps2MarkerIcons
     *
     * @return void
     */
    public function setMaps2MarkerIcons(ObjectStorage $maps2MarkerIcons)
    {
        $this->maps2MarkerIcons = $maps2MarkerIcons;
    }

    /**
     * Returns the maps2MarkerIconWidth
     *
     * @return int $maps2MarkerIconWidth
     */
    public function getMaps2MarkerIconWidth()
    {
        // prevent using local maps2MarkerIconWidth, if no markerIcon is set.
        if (
            empty($this->maps2MarkerIconWidth)
            || (!empty($this->maps2MarkerIconWidth) && $this->getMaps2MarkerIcons()->count() === 0)
        ) {
            /** @var ExtConf $extConf */
            $extConf = GeneralUtility::makeInstance(ExtConf::class);
            return $extConf->getMarkerIconWidth();
        }
        return $this->maps2MarkerIconWidth;
    }

    /**
     * Sets the maps2MarkerIconWidth
     *
     * @param int $maps2MarkerIconWidth
     *
     * @return void
     */
    public function setMaps2MarkerIconWidth($maps2MarkerIconWidth)
    {
        $this->maps2MarkerIconWidth = (int)$maps2MarkerIconWidth;
    }

    /**
     * Returns the maps2MarkerIconHeight
     *
     * @return int $maps2MarkerIconHeight
     */
    public function getMaps2MarkerIconHeight()
    {
        // prevent using local maps2MarkerIconHeight, if no markerIcon is set.
        if (
            empty($this->maps2MarkerIconHeight)
            || (!empty($this->maps2MarkerIconHeight) && $this->getMaps2MarkerIcons()->count() === 0)
        ) {
            /** @var ExtConf $extConf */
            $extConf = GeneralUtility::makeInstance(ExtConf::class);
            return $extConf->getMarkerIconHeight();
        }
        return $this->maps2MarkerIconHeight;
    }

    /**
     * Sets the maps2MarkerIconHeight
     *
     * @param int $maps2MarkerIconHeight
     *
     * @return void
     */
    public function setMaps2MarkerIconHeight($maps2MarkerIconHeight)
    {
        $this->maps2MarkerIconHeight = (int)$maps2MarkerIconHeight;
    }

    /**
     * Returns the maps2MarkerIconAnchorPosX
     *
     * @return int $maps2MarkerIconAnchorPosX
     */
    public function getMaps2MarkerIconAnchorPosX()
    {
        // prevent using local maps2MarkerIconAnchorPosX, if no markerIcon is set.
        if (
            empty($this->maps2MarkerIconAnchorPosX)
            || (!empty($this->maps2MarkerIconAnchorPosX) && $this->getMaps2MarkerIcons()->count() === 0)
        ) {
            /** @var ExtConf $extConf */
            $extConf = GeneralUtility::makeInstance(ExtConf::class);
            return $extConf->getMarkerIconAnchorPosX();
        }
        return $this->maps2MarkerIconAnchorPosX;
    }

    /**
     * Sets the maps2MarkerIconAnchorPosX
     *
     * @param int $maps2MarkerIconAnchorPosX
     *
     * @return void
     */
    public function setMaps2MarkerIconAnchorPosX($maps2MarkerIconAnchorPosX)
    {
        $this->maps2MarkerIconAnchorPosX = (int)$maps2MarkerIconAnchorPosX;
    }

    /**
     * Returns the maps2MarkerIconAnchorPosY
     *
     * @return int $maps2MarkerIconAnchorPosY
     */
    public function getMaps2MarkerIconAnchorPosY()
    {
        // prevent using local maps2MarkerIconAnchorPosY, if no markerIcon is set.
        if (
            empty($this->maps2MarkerIconAnchorPosY)
            || (!empty($this->maps2MarkerIconAnchorPosY) && $this->getMaps2MarkerIcons()->count() === 0)
        ) {
            /** @var ExtConf $extConf */
            $extConf = GeneralUtility::makeInstance(ExtConf::class);
            return $extConf->getMarkerIconAnchorPosY();
        }
        return $this->maps2MarkerIconAnchorPosY;
    }

    /**
     * Sets the maps2MarkerIconAnchorPosY
     *
     * @param int $maps2MarkerIconAnchorPosY
     *
     * @return void
     */
    public function setMaps2MarkerIconAnchorPosY($maps2MarkerIconAnchorPosY)
    {
        $this->maps2MarkerIconAnchorPosY = (int)$maps2MarkerIconAnchorPosY;
    }

    /**
     * Returns the sorting
     *
     * @return int $sorting
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * Sets the sorting
     *
     * @param int $sorting
     *
     * @return void
     */
    public function setSorting($sorting)
    {
        $this->sorting = (int)$sorting;
    }
}

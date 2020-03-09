<?php
declare(strict_types = 1);
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
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Domain Model for PoiCollection
 * This is the main model for markers and radius records
 */
class PoiCollection extends AbstractEntity
{
    /**
     * @var string
     */
    protected $collectionType = '';

    /**
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $address = '';

    /**
     * @var float
     */
    protected $latitude = 0.0;

    /**
     * @var float
     */
    protected $longitude = 0.0;

    /**
     * @var int
     */
    protected $radius = 0;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Maps2\Domain\Model\Poi>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $pois;

    /**
     * @var string
     */
    protected $strokeColor = '';

    /**
     * @var string
     */
    protected $strokeOpacity = '';

    /**
     * @var string
     */
    protected $strokeWeight = '';

    /**
     * @var string
     */
    protected $fillColor = '';

    /**
     * @var string
     */
    protected $fillOpacity = '';

    /**
     * @var string
     */
    protected $infoWindowContent = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $infoWindowImages;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $markerIcons;

    /**
     * @var int
     */
    protected $markerIconWidth = 0;

    /**
     * @var int
     */
    protected $markerIconHeight = 0;

    /**
     * @var int
     */
    protected $markerIconAnchorPosX = 0;

    /**
     * @var int
     */
    protected $markerIconAnchorPosY = 0;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Maps2\Domain\Model\Category>
     */
    protected $categories;

    /**
     * distance
     * this is a helper var. This is not part of the db
     *
     * @var float
     */
    protected $distance = 0.0;

    public function __construct()
    {
        $this->initStorageObjects();
    }

    protected function initStorageObjects()
    {
        $this->pois = new ObjectStorage();
        $this->categories = new ObjectStorage();
        $this->infoWindowImages = new ObjectStorage();
        $this->markerIcons = new ObjectStorage();
    }

    public function getCollectionType(): string
    {
        return $this->collectionType;
    }

    public function setCollectionType(string $collectionType)
    {
        $this->collectionType = $collectionType;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = (float)$latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = (float)$longitude;
    }

    public function getRadius(): int
    {
        return $this->radius;
    }

    public function setRadius(int $radius)
    {
        $this->radius = $radius;
    }

    public function addPoi(Poi $poi)
    {
        $this->pois->attach($poi);
    }

    public function removePoi(Poi $poiToRemove)
    {
        $this->pois->detach($poiToRemove);
    }

    public function getPois(): ObjectStorage
    {
        return $this->pois;
    }

    public function setPois(ObjectStorage $pois)
    {
        $this->pois = $pois;
    }

    public function getStrokeColor(): string
    {
        return $this->strokeColor;
    }

    public function setStrokeColor(string $strokeColor)
    {
        $this->strokeColor = $strokeColor;
    }

    public function getStrokeOpacity(): string
    {
        return $this->strokeOpacity;
    }

    public function setStrokeOpacity(string $strokeOpacity)
    {
        $this->strokeOpacity = $strokeOpacity;
    }

    public function getStrokeWeight(): string
    {
        return $this->strokeWeight;
    }

    public function setStrokeWeight(string $strokeWeight)
    {
        $this->strokeWeight = $strokeWeight;
    }

    public function getFillColor(): string
    {
        return $this->fillColor;
    }

    public function setFillColor(string $fillColor)
    {
        $this->fillColor = $fillColor;
    }

    public function getFillOpacity(): string
    {
        return $this->fillOpacity;
    }

    public function setFillOpacity(string $fillOpacity)
    {
        $this->fillOpacity = $fillOpacity;
    }

    public function getInfoWindowContent(): string
    {
        return $this->infoWindowContent;
    }

    public function setInfoWindowContent(string $infoWindowContent)
    {
        $this->infoWindowContent = $infoWindowContent;
    }

    public function addCategory(Category $category)
    {
        $this->categories->attach($category);
    }

    public function removeCategory(Category $category)
    {
        $this->categories->detach($category);
    }

    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    public function getFirstFoundCategoryWithIcon(): ?Category
    {
        $category = null;
        foreach ($this->getCategories() as $category) {
            if ($category->getMaps2MarkerIcon()) {
                break;
            }
        }
        return $category;
    }

    public function setCategories(ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    public function getInfoWindowImages(): ObjectStorage
    {
        return $this->infoWindowImages;
    }

    public function setInfoWindowImages(ObjectStorage $infoWindowImages)
    {
        $this->infoWindowImages = $infoWindowImages;
    }

    public function addInfoWindowImage(FileReference $fileReference)
    {
        $this->infoWindowImages->attach($fileReference);
    }

    public function removeInfoWindowImage(FileReference $fileReference)
    {
        $this->infoWindowImages->detach($fileReference);
    }

    public function getMarkerIcons(): ObjectStorage
    {
        return $this->markerIcons;
    }

    public function getMarkerIcon(): string
    {
        $markerIcon = '';
        $categoryWithIcon = $this->getFirstFoundCategoryWithIcon();
        if ($categoryWithIcon instanceof Category) {
            $markerIcon = $categoryWithIcon->getMaps2MarkerIcon();
        }

        // override markerIcon, if we have a icon defined here in PoiCollection
        $this->markerIcons->rewind();
        // only one icon is allowed, so current() will give us the first icon
        $iconReference = $this->markerIcons->current();
        if (!$iconReference instanceof FileReference) {
            return $markerIcon;
        }

        $falIconReference = $iconReference->getOriginalResource();
        if (!$falIconReference instanceof \TYPO3\CMS\Core\Resource\FileReference) {
            return $markerIcon;
        }

        $siteUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
        return $siteUrl . $falIconReference->getPublicUrl(false);
    }

    public function setMarkerIcons(ObjectStorage $markerIcons)
    {
        $this->markerIcons = $markerIcons;
    }

    public function addMarkerIcon(FileReference $fileReference)
    {
        $this->markerIcons->attach($fileReference);
    }

    public function removeMarkerIcon(FileReference $fileReference)
    {
        $this->markerIcons->detach($fileReference);
    }

    public function getMarkerIconWidth(): int
    {
        // prevent using local markerIconWidth, if no markerIcon is set.
        if (
            empty($this->markerIconWidth)
            || (!empty($this->markerIconWidth) && $this->getMarkerIcons()->count() === 0)
        ) {
            $categoryWithIcon = $this->getFirstFoundCategoryWithIcon();
            if ($categoryWithIcon instanceof Category) {
                return $categoryWithIcon->getMaps2MarkerIconWidth();
            } else {
                /** @var ExtConf $extConf */
                $extConf = GeneralUtility::makeInstance(ExtConf::class);
                return $extConf->getMarkerIconWidth();
            }
        }
        return $this->markerIconWidth;
    }

    public function setMarkerIconWidth(int $markerIconWidth)
    {
        $this->markerIconWidth = $markerIconWidth;
    }

    public function getMarkerIconHeight(): int
    {
        // prevent using local markerIconHeight, if no markerIcon is set.
        if (
            empty($this->markerIconHeight)
            || (!empty($this->markerIconHeight) && $this->getMarkerIcons()->count() === 0)
        ) {
            $categoryWithIcon = $this->getFirstFoundCategoryWithIcon();
            if ($categoryWithIcon instanceof Category) {
                return $categoryWithIcon->getMaps2MarkerIconHeight();
            } else {
                /** @var ExtConf $extConf */
                $extConf = GeneralUtility::makeInstance(ExtConf::class);
                return $extConf->getMarkerIconHeight();
            }
        }
        return $this->markerIconHeight;
    }

    public function setMarkerIconHeight(int $markerIconHeight)
    {
        $this->markerIconHeight = $markerIconHeight;
    }

    public function getMarkerIconAnchorPosX(): int
    {
        // prevent using local markerIconAnchorPosX, if no markerIcon is set.
        if (
            empty($this->markerIconAnchorPosX)
            || (!empty($this->markerIconAnchorPosX) && $this->getMarkerIcons()->count() === 0)
        ) {
            $categoryWithIcon = $this->getFirstFoundCategoryWithIcon();
            if ($categoryWithIcon instanceof Category) {
                return $categoryWithIcon->getMaps2MarkerIconAnchorPosX();
            } else {
                $extConf = GeneralUtility::makeInstance(ExtConf::class);
                return $extConf->getMarkerIconAnchorPosX();
            }
        }
        return $this->markerIconAnchorPosX;
    }

    public function setMarkerIconAnchorPosX(int $markerIconAnchorPosX)
    {
        $this->markerIconAnchorPosX = $markerIconAnchorPosX;
    }

    public function getMarkerIconAnchorPosY(): int
    {
        // prevent using local markerIconAnchorPosY, if no markerIcon is set.
        if (
            empty($this->markerIconAnchorPosY)
            || (!empty($this->markerIconAnchorPosY) && $this->getMarkerIcons()->count() === 0)
        ) {
            $categoryWithIcon = $this->getFirstFoundCategoryWithIcon();
            if ($categoryWithIcon instanceof Category) {
                return $categoryWithIcon->getMaps2MarkerIconAnchorPosY();
            } else {
                /** @var ExtConf $extConf */
                $extConf = GeneralUtility::makeInstance(ExtConf::class);
                return $extConf->getMarkerIconAnchorPosY();
            }
        }
        return $this->markerIconAnchorPosY;
    }

    public function setMarkerIconAnchorPosY(int $markerIconAnchorPosY)
    {
        $this->markerIconAnchorPosY = $markerIconAnchorPosY;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function setDistance(float $distance)
    {
        $this->distance = $distance;
    }
}

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
     * Collection_type
     *
     * @var string
     */
    protected $collectionType = '';

    /**
     * Title
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $title = '';

    /**
     * Address
     *
     * @var string
     */
    protected $address = '';

    /**
     * Latitude
     *
     * @var float
     */
    protected $latitude = 0.0;

    /**
     * Longitude
     *
     * @var float
     */
    protected $longitude = 0.0;

    /**
     * Radius
     *
     * @var int
     */
    protected $radius = 0;

    /**
     * List of POIs
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Maps2\Domain\Model\Poi>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $pois;

    /**
     * StrokeColor
     *
     * @var string
     */
    protected $strokeColor = '';

    /**
     * StrokeOpacity
     *
     * @var string
     */
    protected $strokeOpacity = '';

    /**
     * StrokeWeight
     *
     * @var string
     */
    protected $strokeWeight = '';

    /**
     * FillColor
     *
     * @var string
     */
    protected $fillColor = '';

    /**
     * FillOpacity
     *
     * @var string
     */
    protected $fillOpacity = '';

    /**
     * infoWindowContent
     *
     * @var string
     */
    protected $infoWindowContent = '';

    /**
     * infoWindowImages
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $infoWindowImages;

    /**
     * markerIcon
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $markerIcons;

    /**
     * markerIconWidth
     *
     * @var int
     */
    protected $markerIconWidth = 0;

    /**
     * markerIconHeight
     *
     * @var int
     */
    protected $markerIconHeight = 0;

    /**
     * markerIconAnchorPosX
     *
     * @var int
     */
    protected $markerIconAnchorPosX = 0;

    /**
     * markerIconAnchorPosY
     *
     * @var int
     */
    protected $markerIconAnchorPosY = 0;

    /**
     * categories
     *
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

    /**
     * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
     */
    protected function initStorageObjects()
    {
        $this->pois = new ObjectStorage();
        $this->categories = new ObjectStorage();
        $this->infoWindowImages = new ObjectStorage();
        $this->markerIcons = new ObjectStorage();
    }

    /**
     * Returns the collectionType
     */
    public function getCollectionType(): string
    {
        return $this->collectionType;
    }

    /**
     * Sets the collectionType
     *
     * @param string $collectionType
     */
    public function setCollectionType(string $collectionType)
    {
        $this->collectionType = $collectionType;
    }

    /**
     * Returns the title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Returns the address
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Sets the address
     *
     * @param string $address
     */
    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    /**
     * Returns the latitude
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * Sets the latitude
     *
     * @param float $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = (float)$latitude;
    }

    /**
     * Returns the longitude
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * Sets the longitude
     *
     * @param float $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = (float)$longitude;
    }

    /**
     * Returns the radius
     */
    public function getRadius(): int
    {
        return $this->radius;
    }

    /**
     * Sets the radius
     *
     * @param int $radius
     */
    public function setRadius(int $radius)
    {
        $this->radius = $radius;
    }

    /**
     * Adds a Poi
     *
     * @param Poi $poi
     */
    public function addPoi(Poi $poi)
    {
        $this->pois->attach($poi);
    }

    /**
     * Removes a Poi
     *
     * @param Poi $poiToRemove
     */
    public function removePoi(Poi $poiToRemove)
    {
        $this->pois->detach($poiToRemove);
    }

    /**
     * Returns the pois
     */
    public function getPois(): ObjectStorage
    {
        return $this->pois;
    }

    /**
     * Sets the pois
     *
     * @param ObjectStorage $pois
     */
    public function setPois(ObjectStorage $pois)
    {
        $this->pois = $pois;
    }

    /**
     * Returns the strokeColor
     */
    public function getStrokeColor(): string
    {
        return $this->strokeColor;
    }

    /**
     * Sets the strokeColor
     *
     * @param string $strokeColor
     */
    public function setStrokeColor(string $strokeColor)
    {
        $this->strokeColor = (string)$strokeColor;
    }

    /**
     * Returns the strokeOpacity
     */
    public function getStrokeOpacity(): string
    {
        return $this->strokeOpacity;
    }

    /**
     * Sets the strokeOpacity
     *
     * @param string $strokeOpacity
     */
    public function setStrokeOpacity(string $strokeOpacity)
    {
        $this->strokeOpacity = $strokeOpacity;
    }

    /**
     * Returns the strokeWeight
     */
    public function getStrokeWeight(): string
    {
        return $this->strokeWeight;
    }

    /**
     * Sets the strokeWeight
     *
     * @param string $strokeWeight
     */
    public function setStrokeWeight(string $strokeWeight)
    {
        $this->strokeWeight = $strokeWeight;
    }

    /**
     * Returns the fillColor
     */
    public function getFillColor(): string
    {
        return $this->fillColor;
    }

    /**
     * Sets the fillColor
     *
     * @param string $fillColor
     */
    public function setFillColor(string $fillColor)
    {
        $this->fillColor = $fillColor;
    }

    /**
     * Returns the fillOpacity
     */
    public function getFillOpacity(): string
    {
        return $this->fillOpacity;
    }

    /**
     * Sets the fillOpacity
     *
     * @param string $fillOpacity
     */
    public function setFillOpacity(string $fillOpacity)
    {
        $this->fillOpacity = $fillOpacity;
    }

    /**
     * Returns the infoWindowContent
     */
    public function getInfoWindowContent(): string
    {
        return $this->infoWindowContent;
    }

    /**
     * Sets the infoWindowContent
     *
     * @param string $infoWindowContent
     */
    public function setInfoWindowContent(string $infoWindowContent)
    {
        $this->infoWindowContent = $infoWindowContent;
    }

    /**
     * Adds a Category
     *
     * @param Category $category
     */
    public function addCategory(Category $category)
    {
        $this->categories->attach($category);
    }

    /**
     * Removes a Category
     *
     * @param Category $category
     */
    public function removeCategory(Category $category)
    {
        $this->categories->detach($category);
    }

    /**
     * Returns the categories
     *
     * @return ObjectStorage|Category[]
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * Returns the first category where a icon is defined
     *
     * @return Category|null
     */
    public function getFirstFoundCategoryWithIcon()
    {
        $category = null;
        foreach ($this->getCategories() as $category) {
            if ($category->getMaps2MarkerIcon()) {
                break;
            }
        }
        return $category;
    }

    /**
     * Sets the categories
     *
     * @param ObjectStorage $categories
     */
    public function setCategories(ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    /**
     * Returns the infoWindowImages
     */
    public function getInfoWindowImages(): ObjectStorage
    {
        return $this->infoWindowImages;
    }

    /**
     * Sets the infoWindowImages
     *
     * @param ObjectStorage $infoWindowImages
     */
    public function setInfoWindowImages(ObjectStorage $infoWindowImages)
    {
        $this->infoWindowImages = $infoWindowImages;
    }

    /**
     * Add a new FileReference to InfoWindowImages
     *
     * @param FileReference $fileReference
     */
    public function addInfoWindowImage(FileReference $fileReference)
    {
        $this->infoWindowImages->attach($fileReference);
    }

    /**
     * Remove a FileReference from InfoWindowImages
     *
     * @param FileReference $fileReference
     */
    public function removeInfoWindowImage(FileReference $fileReference)
    {
        $this->infoWindowImages->detach($fileReference);
    }

    /**
     * Returns the marker icons
     */
    public function getMarkerIcons(): ObjectStorage
    {
        return $this->markerIcons;
    }

    /**
     * Get marker icon
     * It also searches for marker icons in categories
     *
     * @return string|null
     */
    public function getMarkerIcon()
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

    /**
     * Sets the markerIcons
     *
     * @param ObjectStorage $markerIcons
     */
    public function setMarkerIcons(ObjectStorage $markerIcons)
    {
        $this->markerIcons = $markerIcons;
    }

    /**
     * Add a new FileReference to Marker Icons
     *
     * @param FileReference $fileReference
     */
    public function addMarkerIcon(FileReference $fileReference)
    {
        $this->markerIcons->attach($fileReference);
    }

    /**
     * Remove a FileReference from Marker Icons
     *
     * @param FileReference $fileReference
     */
    public function removeMarkerIcon(FileReference $fileReference)
    {
        $this->markerIcons->detach($fileReference);
    }

    /**
     * Returns the markerIconWidth
     *
     * @return int
     */
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

    /**
     * Sets the markerIconWidth
     *
     * @param int $markerIconWidth
     */
    public function setMarkerIconWidth($markerIconWidth)
    {
        $this->markerIconWidth = (int)$markerIconWidth;
    }

    /**
     * Returns the markerIconHeight
     */
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

    /**
     * Sets the markerIconHeight
     *
     * @param int $markerIconHeight
     */
    public function setMarkerIconHeight($markerIconHeight)
    {
        $this->markerIconHeight = (int)$markerIconHeight;
    }

    /**
     * Returns the markerIconAnchorPosX
     */
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

    /**
     * Sets the markerIconAnchorPosX
     *
     * @param int $markerIconAnchorPosX
     */
    public function setMarkerIconAnchorPosX($markerIconAnchorPosX)
    {
        $this->markerIconAnchorPosX = (int)$markerIconAnchorPosX;
    }

    /**
     * Returns the markerIconAnchorPosY
     */
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

    /**
     * Sets the markerIconAnchorPosY
     *
     * @param int $markerIconAnchorPosY
     */
    public function setMarkerIconAnchorPosY($markerIconAnchorPosY)
    {
        $this->markerIconAnchorPosY = (int)$markerIconAnchorPosY;
    }

    /**
     * Returns the distance
     */
    public function getDistance(): float
    {
        return $this->distance;
    }

    /**
     * Sets the distance
     *
     * @param float $distance
     */
    public function setDistance($distance)
    {
        $this->distance = (float)$distance;
    }
}

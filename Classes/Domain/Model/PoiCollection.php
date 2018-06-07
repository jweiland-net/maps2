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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class PoiCollection
 *
 * @category Domain/Model
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
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
     * @validate NotEmpty
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
     * LatitudeOrig
     *
     * @var float
     */
    protected $latitudeOrig = 0.0;

    /**
     * LongitudeOrig
     *
     * @var float
     */
    protected $longitudeOrig = 0.0;

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
     * @cascade remove
     * @lazy
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

    /**
     * Constructor of this model class
     */
    public function __construct()
    {
        $this->initStorageObjects();
    }

    /**
     * Initializes all Tx_Extbase_Persistence_ObjectStorage properties.
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->pois = new ObjectStorage();
        $this->categories = new ObjectStorage();
        $this->markerIcons = new ObjectStorage();
    }

    /**
     * Returns the collectionType
     *
     * @return string $collectionType
     */
    public function getCollectionType()
    {
        return $this->collectionType;
    }

    /**
     * Sets the collectionType
     *
     * @param string $collectionType
     * @return void
     */
    public function setCollectionType($collectionType)
    {
        $this->collectionType = (string)$collectionType;
    }

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;
    }

    /**
     * Returns the address
     *
     * @return string $address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the address
     *
     * @param string $address
     * @return void
     */
    public function setAddress($address)
    {
        $this->address = (string)$address;
    }

    /**
     * Returns the latitude
     *
     * @return float $latitude
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Sets the latitude
     *
     * @param float $latitude
     * @return void
     */
    public function setLatitude($latitude)
    {
        $this->latitude = (float)$latitude;
    }

    /**
     * Returns the longitude
     *
     * @return float $longitude
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Sets the longitude
     *
     * @param float $longitude
     * @return void
     */
    public function setLongitude($longitude)
    {
        $this->longitude = (float)$longitude;
    }

    /**
     * Returns the latitudeOrig
     *
     * @return float $latitudeOrig
     */
    public function getLatitudeOrig()
    {
        return $this->latitudeOrig;
    }

    /**
     * Sets the latitudeOrig
     *
     * @param float $latitudeOrig
     * @return void
     */
    public function setLatitudeOrig($latitudeOrig)
    {
        $this->latitudeOrig = (float)$latitudeOrig;
    }

    /**
     * Returns the longitudeOrig
     *
     * @return float $longitudeOrig
     */
    public function getLongitudeOrig()
    {
        return $this->longitudeOrig;
    }

    /**
     * Sets the longitudeOrig
     *
     * @param float $longitudeOrig
     * @return void
     */
    public function setLongitudeOrig($longitudeOrig)
    {
        $this->longitudeOrig = (float)$longitudeOrig;
    }

    /**
     * Returns the radius
     *
     * @return int $radius
     */
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * Sets the radius
     *
     * @param int $radius
     * @return void
     */
    public function setRadius($radius)
    {
        $this->radius = (int)$radius;
    }

    /**
     * Adds a Poi
     *
     * @param Poi $poi
     * @return void
     */
    public function addPoi(Poi $poi)
    {
        $this->pois->attach($poi);
    }

    /**
     * Removes a Poi
     *
     * @param Poi $poiToRemove The Poi to be removed
     * @return void
     */
    public function removePoi(Poi $poiToRemove)
    {
        $this->pois->detach($poiToRemove);
    }

    /**
     * Returns the pois
     *
     * @return ObjectStorage $pois
     */
    public function getPois()
    {
        return $this->pois;
    }

    /**
     * Sets the pois
     *
     * @param ObjectStorage $pois
     * @return void
     */
    public function setPois(ObjectStorage $pois)
    {
        $this->pois = $pois;
    }

    /**
     * Returns the strokeColor
     *
     * @return string $strokeColor
     */
    public function getStrokeColor()
    {
        return $this->strokeColor;
    }

    /**
     * Sets the strokeColor
     *
     * @param string $strokeColor
     * @return void
     */
    public function setStrokeColor($strokeColor)
    {
        $this->strokeColor = (string)$strokeColor;
    }

    /**
     * Returns the strokeOpacity
     *
     * @return string $strokeOpacity
     */
    public function getStrokeOpacity()
    {
        return $this->strokeOpacity;
    }

    /**
     * Sets the strokeOpacity
     *
     * @param string $strokeOpacity
     * @return void
     */
    public function setStrokeOpacity($strokeOpacity)
    {
        $this->strokeOpacity = (string)$strokeOpacity;
    }

    /**
     * Returns the strokeWeight
     *
     * @return string $strokeWeight
     */
    public function getStrokeWeight()
    {
        return $this->strokeWeight;
    }

    /**
     * Sets the strokeWeight
     *
     * @param string $strokeWeight
     * @return void
     */
    public function setStrokeWeight($strokeWeight)
    {
        $this->strokeWeight = (string)$strokeWeight;
    }

    /**
     * Returns the fillColor
     *
     * @return string $fillColor
     */
    public function getFillColor()
    {
        return $this->fillColor;
    }

    /**
     * Sets the fillColor
     *
     * @param string $fillColor
     * @return void
     */
    public function setFillColor($fillColor)
    {
        $this->fillColor = (string)$fillColor;
    }

    /**
     * Returns the fillOpacity
     *
     * @return string $fillOpacity
     */
    public function getFillOpacity()
    {
        return $this->fillOpacity;
    }

    /**
     * Sets the fillOpacity
     *
     * @param string $fillOpacity
     * @return void
     */
    public function setFillOpacity($fillOpacity)
    {
        $this->fillOpacity = (string)$fillOpacity;
    }

    /**
     * Returns the infoWindowContent
     *
     * @return string $infoWindowContent
     */
    public function getInfoWindowContent()
    {
        return $this->infoWindowContent;
    }

    /**
     * Sets the infoWindowContent
     *
     * @param string $infoWindowContent
     * @return void
     */
    public function setInfoWindowContent($infoWindowContent)
    {
        $this->infoWindowContent = (string)$infoWindowContent;
    }

    /**
     * Adds a Category
     *
     * @param Category $category
     * @return void
     */
    public function addCategory(Category $category)
    {
        $this->categories->attach($category);
    }

    /**
     * Removes a Category
     *
     * @param Category $category The Category to be removed
     * @return void
     */
    public function removeCategory(Category $category)
    {
        $this->categories->detach($category);
    }

    /**
     * Returns the categories
     *
     * @return ObjectStorage|Category[] $categories
     */
    public function getCategories()
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
     * @return void
     */
    public function setCategories(ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    /**
     * Returns the markerIcons
     *
     * @return ObjectStorage $markerIcons
     */
    public function getMarkerIcons()
    {
        return $this->markerIcons;
    }

    /**
     * Get marker icon
     * It also searches for marker icons in categories
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

        return $falIconReference->getPublicUrl(false);
    }

    /**
     * Sets the markerIcons
     *
     * @param ObjectStorage $markerIcons
     * @return void
     */
    public function setMarkerIcons(ObjectStorage $markerIcons)
    {
        $this->markerIcons = $markerIcons;
    }

    /**
     * Add a new FileReference to Marker Icons
     *
     * @param FileReference $fileReference
     * @return void
     */
    public function addMarkerIcon(FileReference $fileReference)
    {
        $this->markerIcons->attach($fileReference);
    }

    /**
     * Remove a FileReference from Marker Icons
     *
     * @param FileReference $fileReference
     * @return void
     */
    public function removeMarkerIcon(FileReference $fileReference)
    {
        $this->markerIcons->detach($fileReference);
    }

    /**
     * Returns the markerIconWidth
     *
     * @return int $markerIconWidth
     */
    public function getMarkerIconWidth()
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
     *
     * @return void
     */
    public function setMarkerIconWidth($markerIconWidth)
    {
        $this->markerIconWidth = (int)$markerIconWidth;
    }

    /**
     * Returns the markerIconHeight
     *
     * @return int $markerIconHeight
     */
    public function getMarkerIconHeight()
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
     *
     * @return void
     */
    public function setMarkerIconHeight($markerIconHeight)
    {
        $this->markerIconHeight = (int)$markerIconHeight;
    }

    /**
     * Returns the markerIconAnchorPosX
     *
     * @return int $markerIconAnchorPosX
     */
    public function getMarkerIconAnchorPosX()
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
                /** @var ExtConf $extConf */
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
     *
     * @return void
     */
    public function setMarkerIconAnchorPosX($markerIconAnchorPosX)
    {
        $this->markerIconAnchorPosX = (int)$markerIconAnchorPosX;
    }

    /**
     * Returns the markerIconAnchorPosY
     *
     * @return int $markerIconAnchorPosY
     */
    public function getMarkerIconAnchorPosY()
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
     *
     * @return void
     */
    public function setMarkerIconAnchorPosY($markerIconAnchorPosY)
    {
        $this->markerIconAnchorPosY = (int)$markerIconAnchorPosY;
    }

    /**
     * Returns the distance
     *
     * @return float $distance
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Sets the distance
     *
     * @param float $distance
     * @return void
     */
    public function setDistance($distance)
    {
        $this->distance = (float)$distance;
    }
}

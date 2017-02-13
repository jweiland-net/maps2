<?php
namespace JWeiland\Maps2\Domain\Model;

/**
 * This file is part of the TYPO3 CMS project.
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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class PoiCollection
 *
 * @category Domain/Model
 * @package  Maps2
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
    protected $latitude = 0;

    /**
     * Longitude
     *
     * @var float
     */
    protected $longitude = 0;

    /**
     * LatitudeOrig
     *
     * @var float
     */
    protected $latitudeOrig = 0;

    /**
     * LongitudeOrig
     *
     * @var float
     */
    protected $longitudeOrig = 0;

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
    protected $pois = null;

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
     * categories
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Maps2\Domain\Model\Category>
     */
    protected $categories = null;

    /**
     * distance
     * this is a helper var. This is not part of the db
     *
     * @var float
     */
    protected $distance = 0;

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
        $this->collectionType = $collectionType;
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
        $this->title = $title;
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
        $this->address = $address;
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
        $this->latitude = $latitude;
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
        $this->longitude = $longitude;
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
        $this->latitudeOrig = $latitudeOrig;
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
        $this->longitudeOrig = $longitudeOrig;
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
        $this->radius = $radius;
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
     * @param int $infoWindowContent
     * @return void
     */
    public function setInfoWindowContent($infoWindowContent)
    {
        $this->infoWindowContent = $infoWindowContent;
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
     * @return ObjectStorage $categories
     */
    public function getCategories()
    {
        return $this->categories;
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
        $this->distance = $distance;
    }
}

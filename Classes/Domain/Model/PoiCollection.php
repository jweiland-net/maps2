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
     * @var string
     */
    protected $latitude = '0';

    /**
     * Longitude
     *
     * @var string
     */
    protected $longitude = '0';

    /**
     * LatitudeOrig
     *
     * @var string
     */
    protected $latitudeOrig = '0';

    /**
     * LongitudeOrig
     *
     * @var string
     */
    protected $longitudeOrig = '0';

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
     * infoWindowContent
     *
     * @var string
     */
    protected $infoWindowContent = '';

    /**
     * infoWindowOpenClose
     *
     * @var bool
     */
    protected $infoWindowOpenClose = false;

    /**
     * categories
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    protected $categories = null;

    /**
     * distance
     * this is a helper var. This is not part of the db
     *
     * @var int
     */
    protected $distance = 0;

    /**
     * contructor of this model class
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
     * @return string $latitude
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Sets the latitude
     *
     * @param string $latitude
     * @return void
     */
    public function setLatitude($latitude)
    {
        $this->latitude = number_format((float)$latitude, 6, '.', '');
    }

    /**
     * Returns the longitude
     *
     * @return string $longitude
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Sets the longitude
     *
     * @param string $longitude
     * @return void
     */
    public function setLongitude($longitude)
    {
        $this->longitude = number_format((float)$longitude, 6, '.', '');
    }

    /**
     * Returns the latitudeOrig
     *
     * @return string $latitudeOrig
     */
    public function getLatitudeOrig()
    {
        return $this->latitudeOrig;
    }

    /**
     * Sets the latitudeOrig
     *
     * @param string $latitudeOrig
     * @return void
     */
    public function setLatitudeOrig($latitudeOrig)
    {
        $this->latitudeOrig = number_format((float)$latitudeOrig, 6, '.', '');
    }

    /**
     * Returns the longitudeOrig
     *
     * @return string $longitudeOrig
     */
    public function getLongitudeOrig()
    {
        return $this->longitudeOrig;
    }

    /**
     * Sets the longitudeOrig
     *
     * @param string $longitudeOrig
     * @return void
     */
    public function setLongitudeOrig($longitudeOrig)
    {
        $this->longitudeOrig = number_format((float)$longitudeOrig, 6, '.', '');
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
     * @param \JWeiland\Maps2\Domain\Model\Poi $poi
     * @return void
     */
    public function addPoi(\JWeiland\Maps2\Domain\Model\Poi $poi)
    {
        $this->pois->attach($poi);
    }

    /**
     * Removes a Poi
     *
     * @param \JWeiland\Maps2\Domain\Model\Poi $poiToRemove The Poi to be removed
     * @return void
     */
    public function removePoi(\JWeiland\Maps2\Domain\Model\Poi $poiToRemove)
    {
        $this->pois->detach($poiToRemove);
    }

    /**
     * Returns the pois
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $pois
     */
    public function getPois()
    {
        return $this->pois;
    }

    /**
     * Sets the pois
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $pois
     * @return void
     */
    public function setPois(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $pois)
    {
        $this->pois = $pois;
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
     * Returns the infoWindowOpenClose
     *
     * @return bool $infoWindowOpenClose
     */
    public function getInfoWindowOpenClose()
    {
        return $this->infoWindowOpenClose;
    }

    /**
     * Sets the infoWindowOpenClose
     *
     * @param bool $infoWindowOpenClose
     * @return void
     */
    public function setInfoWindowOpenClose($infoWindowOpenClose)
    {
        $this->infoWindowOpenClose = (bool)$infoWindowOpenClose;
    }

    /**
     * Adds a Category
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\Category $category
     * @return void
     */
    public function addCategory(\TYPO3\CMS\Extbase\Domain\Model\Category $category)
    {
        $this->categories->attach($category);
    }

    /**
     * Removes a Category
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\Category $category The Category to be removed
     * @return void
     */
    public function removeCategory(\TYPO3\CMS\Extbase\Domain\Model\Category $category)
    {
        $this->categories->detach($category);
    }

    /**
     * Returns the categories
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Sets the categories
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     * @return void
     */
    public function setCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    /**
     * Returns the distance
     *
     * @return int $distance
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Sets the distance
     *
     * @param int $distance
     * @return void
     */
    public function setDistance($distance)
    {
        $this->distance = (int)$distance;
    }
}

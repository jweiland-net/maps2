<?php
namespace JWeiland\Maps2\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <sfroemken@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PoiCollection extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Collection_type
	 *
	 * @var string
	 */
	protected $collectionType;

	/**
	 * Title
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $title;

	/**
	 * Address
	 *
	 * @var string
	 */
	protected $address;

	/**
	 * Latitude
	 *
	 * @var float
	 */
	protected $latitude;

	/**
	 * Longitude
	 *
	 * @var float
	 */
	protected $longitude;

	/**
	 * LatitudeOrig
	 *
	 * @var float
	 */
	protected $latitudeOrig;

	/**
	 * LongitudeOrig
	 *
	 * @var float
	 */
	protected $longitudeOrig;

	/**
	 * Radius
	 *
	 * @var integer
	 */
	protected $radius;

	/**
	 * List of POIs
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Maps2\Domain\Model\Poi>
	 * @cascade remove
	 * @lazy
	 */
	protected $pois;

	/**
	 * infoWindowContent
	 *
	 * @var string
	 */
	protected $infoWindowContent;

	/**
	 * infoWindowOpenClose
	 *
	 * @var boolean
	 */
	protected $infoWindowOpenClose;

	/**
	 * categories
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
	 */
	protected $categories;

	/**
	 * distance
	 * this is a helper var. This is not part of the db
	 *
	 * @var float
	 */
	protected $distance;





	/**
	 * contructor of this model class
	 */
	public function __construct() {
		$this->initStorageObjects();
	}

	/**
	 * Initializes all Tx_Extbase_Persistence_ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->pois = new ObjectStorage();
		$this->categories = new ObjectStorage();
	}

	/**
	 * Returns the collectionType
	 *
	 * @return string $collectionType
	 */
	public function getCollectionType() {
		return $this->collectionType;
	}

	/**
	 * Sets the collectionType
	 *
	 * @param string $collectionType
	 * @return void
	 */
	public function setCollectionType($collectionType) {
		$this->collectionType = $collectionType;
	}

	/**
	 * Returns the title
	 *
	 * @return string $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Returns the address
	 *
	 * @return string $address
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * Sets the address
	 *
	 * @param string $address
	 * @return void
	 */
	public function setAddress($address) {
		$this->address = $address;
	}

	/**
	 * Returns the latitude
	 *
	 * @return float $latitude
	 */
	public function getLatitude() {
		return $this->latitude;
	}

	/**
	 * Sets the latitude
	 *
	 * @param float $latitude
	 * @return void
	 */
	public function setLatitude($latitude) {
		$this->latitude = $latitude;
	}

	/**
	 * Returns the longitude
	 *
	 * @return float $longitude
	 */
	public function getLongitude() {
		return $this->longitude;
	}

	/**
	 * Sets the longitude
	 *
	 * @param float $longitude
	 * @return void
	 */
	public function setLongitude($longitude) {
		$this->longitude = $longitude;
	}

	/**
	 * Returns the latitudeOrig
	 *
	 * @return float $latitudeOrig
	 */
	public function getLatitudeOrig() {
		return $this->latitudeOrig;
	}

	/**
	 * Sets the latitudeOrig
	 *
	 * @param float $latitudeOrig
	 * @return void
	 */
	public function setLatitudeOrig($latitudeOrig) {
		$this->latitudeOrig = $latitudeOrig;
	}

	/**
	 * Returns the longitudeOrig
	 *
	 * @return float $longitudeOrig
	 */
	public function getLongitudeOrig() {
		return $this->longitudeOrig;
	}

	/**
	 * Sets the longitudeOrig
	 *
	 * @param float $longitudeOrig
	 * @return void
	 */
	public function setLongitudeOrig($longitudeOrig) {
		$this->longitudeOrig = $longitudeOrig;
	}

	/**
	 * Returns the radius
	 *
	 * @return integer $radius
	 */
	public function getRadius() {
		return $this->radius;
	}

	/**
	 * Sets the radius
	 *
	 * @param integer $radius
	 * @return void
	 */
	public function setRadius($radius) {
		$this->radius = $radius;
	}

	/**
	 * Adds a Poi
	 *
	 * @param \JWeiland\Maps2\Domain\Model\Poi $poi
	 * @return void
	 */
	public function addPoi(\JWeiland\Maps2\Domain\Model\Poi $poi) {
		$this->pois->attach($poi);
	}

	/**
	 * Removes a Poi
	 *
	 * @param \JWeiland\Maps2\Domain\Model\Poi $poiToRemove The Poi to be removed
	 * @return void
	 */
	public function removePoi(\JWeiland\Maps2\Domain\Model\Poi $poiToRemove) {
		$this->pois->detach($poiToRemove);
	}

	/**
	 * Returns the pois
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $pois
	 */
	public function getPois() {
		return $this->pois;
	}

	/**
	 * Sets the pois
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $pois
	 * @return void
	 */
	public function setPois(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $pois) {
		$this->pois = $pois;
	}

	/**
	 * Returns the infoWindowContent
	 *
	 * @return string $infoWindowContent
	 */
	public function getInfoWindowContent() {
		return $this->infoWindowContent;
	}

	/**
	 * Sets the infoWindowContent
	 *
	 * @param integer $infoWindowContent
	 * @return void
	 */
	public function setInfoWindowContent($infoWindowContent) {
		$this->infoWindowContent = $infoWindowContent;
	}

	/**
	 * Returns the infoWindowOpenClose
	 *
	 * @return boolean $infoWindowOpenClose
	 */
	public function getInfoWindowOpenClose() {
		return $this->infoWindowOpenClose;
	}

	/**
	 * Sets the infoWindowOpenClose
	 *
	 * @param boolean $infoWindowOpenClose
	 * @return void
	 */
	public function setInfoWindowOpenClose($infoWindowOpenClose) {
		$this->infoWindowOpenClose = $infoWindowOpenClose;
	}

	/**
	 * Adds a Category
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\Category $category
	 * @return void
	 */
	public function addCategory(\TYPO3\CMS\Extbase\Domain\Model\Category $category) {
		$this->categories->attach($category);
	}

	/**
	 * Removes a Category
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\Category $category The Category to be removed
	 * @return void
	 */
	public function removeCategory(\TYPO3\CMS\Extbase\Domain\Model\Category $category) {
		$this->categories->detach($category);
	}

	/**
	 * Returns the categories
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
	 */
	public function getCategories() {
		return $this->categories;
	}

	/**
	 * Sets the categories
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
	 * @return void
	 */
	public function setCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories) {
		$this->categories = $categories;
	}

	/**
	 * Returns the distance
	 *
	 * @return float $distance
	 */
	public function getDistance() {
		return $this->distance;
	}

	/**
	 * Sets the distance
	 *
	 * @param float $distance
	 * @return void
	 */
	public function setDistance($distance) {
		$this->distance = $distance;
	}

}
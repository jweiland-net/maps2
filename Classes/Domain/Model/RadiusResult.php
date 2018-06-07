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

use JWeiland\Maps2\Domain\Model\RadiusResult\AddressComponent;
use JWeiland\Maps2\Domain\Model\RadiusResult\Geometry;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class RadiusResult
 *
 * @category Domain/Model
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class RadiusResult
{
    /**
     * addressComponents
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Maps2\Domain\Model\RadiusResult\AddressComponent>
     */
    protected $addressComponents;

    /**
     * formattedAddress
     *
     * @var string
     */
    protected $formattedAddress = '';

    /**
     * geometry
     *
     * @var \JWeiland\Maps2\Domain\Model\RadiusResult\Geometry
     */
    protected $geometry;

    /**
     * types
     *
     * @var array
     */
    protected $types = [];

    /**
     * poiCollections
     *
     * @var array
     */
    protected $poiCollections = [];

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
        $this->addressComponents = new ObjectStorage();
    }

    /**
     * Getter for addressComponents
     *
     * @return ObjectStorage
     */
    public function getAddressComponents()
    {
        return $this->addressComponents;
    }

    /**
     * Setter for addressComponents
     *
     * @param ObjectStorage $addressComponents
     */
    public function setAddressComponents(ObjectStorage $addressComponents)
    {
        $this->addressComponents = $addressComponents;
    }

    /**
     * Add address component
     *
     * @param AddressComponent $addressComponent
     */
    public function addAddressComponent(AddressComponent $addressComponent)
    {
        $this->addressComponents->attach($addressComponent);
    }

    /**
     * Remove address component
     *
     * @param AddressComponent $addressComponent
     */
    public function removeAddressComponent(AddressComponent $addressComponent)
    {
        $this->addressComponents->detach($addressComponent);
    }

    /**
     * Getter for formattedAddress
     *
     * @return string
     */
    public function getFormattedAddress()
    {
        return $this->formattedAddress;
    }

    /**
     * Setter for formattedAddress
     *
     * @param string $formattedAddress
     */
    public function setFormattedAddress($formattedAddress)
    {
        $this->formattedAddress = (string)$formattedAddress;
    }

    /**
     * Getter for geometry
     *
     * @return Geometry
     */
    public function getGeometry()
    {
        return $this->geometry;
    }

    /**
     * Setter for geometry
     *
     * @param Geometry $geometry
     */
    public function setGeometry(Geometry $geometry)
    {
        $this->geometry = $geometry;
    }

    /**
     * Getter for types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Setter for Types
     *
     * @param array $types
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * Getter for poiCollections
     *
     * @return array
     */
    public function getPoiCollections()
    {
        return $this->poiCollections;
    }

    /**
     * Setter for poiCollections
     *
     * @param array $poiCollections
     */
    public function setPoiCollections(array $poiCollections)
    {
        $this->poiCollections = $poiCollections;
    }
}

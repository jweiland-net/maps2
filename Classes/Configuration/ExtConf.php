<?php
namespace JWeiland\Maps2\Configuration;

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

use TYPO3\CMS\Core\SingletonInterface;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ExtConf implements SingletonInterface
{
    /**
     * use https
     *
     * @var bool
     */
    protected $useHttps = false;

    /**
     * google maps library
     *
     * @var string
     */
    protected $googleMapsLibrary = '';

    /**
     * Google Maps JavaScript ApiKey
     *
     * @var string
     */
    protected $googleMapsJavaScriptApiKey = '';

    /**
     * Google Maps Geocode ApiKey
     *
     * @var string
     */
    protected $googleMapsGeocodeApiKey = '';

    /**
     * @var bool
     */
    protected $explicitAllowGoogleMaps = false;

    /**
     * @var bool
     */
    protected $explicitAllowGoogleMapsBySessionOnly = false;

    /**
     * default latitude
     *
     * @var float
     */
    protected $defaultLatitude;

    /**
     * default longitude
     *
     * @var float
     */
    protected $defaultLongitude;

    /**
     * default radius
     *
     * @var int
     */
    protected $defaultRadius = 0;

    /**
     * infoWindowContentTemplatePath
     *
     * @var string
     */
    protected $infoWindowContentTemplatePath = '';

    /**
     * allowMapTemplatePath
     *
     * @var string
     */
    protected $allowMapTemplatePath = '';

    /**
     * stroke color
     *
     * @var string
     */
    protected $strokeColor = '';

    /**
     * stroke opacity
     *
     * @var float
     */
    protected $strokeOpacity;

    /**
     * stroke weight
     *
     * @var int
     */
    protected $strokeWeight = 0;

    /**
     * fill color
     *
     * @var string
     */
    protected $fillColor = '';

    /**
     * fill opacity
     *
     * @var float
     */
    protected $fillOpacity;

    /**
     * constructor of this class
     * This method reads the global configuration and calls the setter methods
     */
    public function __construct()
    {
        // get global configuration
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['maps2']);
        if (is_array($extConf) && count($extConf)) {
            // call setter method foreach configuration entry
            foreach ($extConf as $key => $value) {
                $methodName = 'set' . ucfirst($key);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($value);
                }
            }
        }
    }

    /**
     * getter for useHttps
     *
     * @return bool
     */
    public function getUseHttps()
    {
        if (empty($this->useHttps)) {
            return false;
        } else {
            return $this->useHttps;
        }
    }

    /**
     * setter for useHttps
     *
     * @param bool $useHttps
     * @return void
     */
    public function setUseHttps($useHttps)
    {
        $this->useHttps = (bool)$useHttps;
    }

    /**
     * getter for googleMapsLibrary
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getGoogleMapsLibrary()
    {
        if (trim($this->googleMapsLibrary) === '|') {
            $library = 'https://maps.googleapis.com/maps/api/js?key=|&callback=initMap';
        } else {
            $library = $this->googleMapsLibrary;
        }
        // insert ApiKey
        $library = str_replace('|', $this->getGoogleMapsJavaScriptApiKey(), $library);
        // $parts: 0 = full string; 1 = s or empty; 2 = needed url
        preg_match('|^http(s)?://(.*)$|i', $library, $parts);
        if ($this->getUseHttps()) {
            return 'https://' . $parts[2];
        } else {
            return 'http://' . $parts[2];
        }
    }

    /**
     * setter for google maps library
     *
     * @param string $googleMapsLibrary
     * @return void
     */
    public function setGoogleMapsLibrary($googleMapsLibrary)
    {
        $this->googleMapsLibrary = trim($googleMapsLibrary);
    }

    /**
     * Returns the googleMapsJavaScriptApiKey
     *
     * @return string $googleMapsJavaScriptApiKey
     *
     * @throws \Exception
     */
    public function getGoogleMapsJavaScriptApiKey()
    {
        if (empty($this->googleMapsJavaScriptApiKey)) {
            throw new \Exception('You have forgotten to set a JavaScript ApiKey in Extensionmanager.', 1464871544);
        }
        return $this->googleMapsJavaScriptApiKey;
    }

    /**
     * Sets the googleMapsJavaScriptApiKey
     *
     * @param string $googleMapsJavaScriptApiKey
     * @return void
     */
    public function setGoogleMapsJavaScriptApiKey($googleMapsJavaScriptApiKey)
    {
        $this->googleMapsJavaScriptApiKey = trim((string)$googleMapsJavaScriptApiKey);
    }

    /**
     * Returns the googleMapsGeocodeApiKey
     *
     * @return string $googleMapsGeocodeApiKey
     *
     * @throws \Exception
     */
    public function getGoogleMapsGeocodeApiKey()
    {
        if (empty($this->googleMapsGeocodeApiKey)) {
            throw new \Exception('You have forgotten to set a Geocode ApiKey in Extensionmanager.', 1464871560);
        }
        if ($this->googleMapsGeocodeApiKey === $this->googleMapsJavaScriptApiKey) {
            throw new \Exception('The ApiKeys for JavaScript APi and Geocode API can not be the same.', 1464871571);
        }
        return $this->googleMapsGeocodeApiKey;
    }

    /**
     * Sets the googleMapsGeocodeApiKey
     *
     * @param string $googleMapsGeocodeApiKey
     * @return void
     */
    public function setGoogleMapsGeocodeApiKey($googleMapsGeocodeApiKey)
    {
        $this->googleMapsGeocodeApiKey = trim((string)$googleMapsGeocodeApiKey);
    }

    /**
     * Returns the explicitAllowGoogleMaps
     *
     * @return bool $explicitAllowGoogleMaps
     */
    public function getExplicitAllowGoogleMaps()
    {
        return $this->explicitAllowGoogleMaps;
    }

    /**
     * Sets the explicitAllowGoogleMaps
     *
     * @param bool $explicitAllowGoogleMaps
     *
     * @return void
     */
    public function setExplicitAllowGoogleMaps($explicitAllowGoogleMaps)
    {
        $this->explicitAllowGoogleMaps = (bool)$explicitAllowGoogleMaps;
    }

    /**
     * Returns the explicitAllowGoogleMapsBySessionOnly
     *
     * @return bool $explicitAllowGoogleMapsBySessionOnly
     */
    public function getExplicitAllowGoogleMapsBySessionOnly()
    {
        return $this->explicitAllowGoogleMapsBySessionOnly;
    }

    /**
     * Sets the explicitAllowGoogleMapsBySessionOnly
     *
     * @param bool $explicitAllowGoogleMapsBySessionOnly
     *
     * @return void
     */
    public function setExplicitAllowGoogleMapsBySessionOnly($explicitAllowGoogleMapsBySessionOnly)
    {
        $this->explicitAllowGoogleMapsBySessionOnly = (bool)$explicitAllowGoogleMapsBySessionOnly;
    }

    /**
     * getter for defaultLatitude
     *
     * @return float
     */
    public function getDefaultLatitude()
    {
        if (empty($this->defaultLatitude)) {
            return 0.00;
        } else {
            return $this->defaultLatitude;
        }
    }

    /**
     * setter for defaultLatitude
     *
     * @param float $defaultLatitude
     * @return void
     */
    public function setDefaultLatitude($defaultLatitude)
    {
        $this->defaultLatitude = (float)$defaultLatitude;
    }

    /**
     * getter for defaultLongitude
     *
     * @return float
     */
    public function getDefaultLongitude()
    {
        if (empty($this->defaultLongitude)) {
            return 0.00;
        } else {
            return $this->defaultLongitude;
        }
    }

    /**
     * setter for defaultLongitude
     *
     * @param float $defaultLongitude
     * @return void
     */
    public function setDefaultLongitude($defaultLongitude)
    {
        $this->defaultLongitude = (float)$defaultLongitude;
    }

    /**
     * getter for defaultRadius
     *
     * @return int
     */
    public function getDefaultRadius()
    {
        if (empty($this->defaultRadius)) {
            return 250;
        } else {
            return $this->defaultRadius;
        }
    }

    /**
     * setter for defaultRadius
     *
     * @param int $defaultRadius
     * @return void
     */
    public function setDefaultRadius($defaultRadius)
    {
        $this->defaultRadius = (int)$defaultRadius;
    }

    /**
     * Returns the infoWindowContentTemplatePath
     *
     * @return string $infoWindowContentTemplatePath
     */
    public function getInfoWindowContentTemplatePath()
    {
        if (empty($this->infoWindowContentTemplatePath)) {
            $this->infoWindowContentTemplatePath = 'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html';
        }
        return $this->infoWindowContentTemplatePath;
    }

    /**
     * Sets the infoWindowContentTemplatePath
     *
     * @param string $infoWindowContentTemplatePath
     * @return void
     */
    public function setInfoWindowContentTemplatePath($infoWindowContentTemplatePath)
    {
        $this->infoWindowContentTemplatePath = (string)$infoWindowContentTemplatePath;
    }

    /**
     * Returns the allowMapTemplatePath
     *
     * @return string $allowMapTemplatePath
     */
    public function getAllowMapTemplatePath()
    {
        if (empty($this->allowMapTemplatePath)) {
            $this->allowMapTemplatePath = 'EXT:maps2/Resources/Private/Templates/AllowMapForm.html';
        }
        return $this->allowMapTemplatePath;
    }

    /**
     * Sets the allowMapTemplatePath
     *
     * @param string $allowMapTemplatePath
     *
     * @return void
     */
    public function setAllowMapTemplatePath($allowMapTemplatePath)
    {
        $this->allowMapTemplatePath = (string)$allowMapTemplatePath;
    }

    /**
     * getter for strokeColor
     *
     * @return string
     */
    public function getStrokeColor()
    {
        if (empty($this->strokeColor)) {
            return '#FF0000';
        } else {
            return $this->strokeColor;
        }
    }

    /**
     * setter for strokeColor
     *
     * @param string $strokeColor
     * @return void
     */
    public function setStrokeColor($strokeColor)
    {
        $this->strokeColor = (string)$strokeColor;
    }

    /**
     * getter for strokeOpacity
     *
     * @return float
     */
    public function getStrokeOpacity()
    {
        if (empty($this->strokeOpacity)) {
            return 0.8;
        } else {
            return $this->strokeOpacity;
        }
    }

    /**
     * setter for strokeOpacity
     *
     * @param float $strokeOpacity
     * @return void
     */
    public function setStrokeOpacity($strokeOpacity)
    {
        $this->strokeOpacity = (float)$strokeOpacity;
    }

    /**
     * getter for strokeWeight
     *
     * @return int
     */
    public function getStrokeWeight()
    {
        if (empty($this->strokeWeight)) {
            return 2;
        } else {
            return $this->strokeWeight;
        }
    }

    /**
     * setter for strokeWeight
     *
     * @param int $strokeWeight
     * @return void
     */
    public function setStrokeWeight($strokeWeight)
    {
        $this->strokeWeight = (int)$strokeWeight;
    }

    /**
     * getter for fillColor
     *
     * @return string
     */
    public function getFillColor()
    {
        if (empty($this->fillColor)) {
            return '#FF0000';
        } else {
            return $this->fillColor;
        }
    }

    /**
     * setter for fillColor
     *
     * @param string $fillColor
     * @return void
     */
    public function setFillColor($fillColor)
    {
        $this->fillColor = (string)$fillColor;
    }

    /**
     * getter for fillOpacity
     *
     * @return float
     */
    public function getFillOpacity()
    {
        if (empty($this->fillOpacity)) {
            return 0.35;
        } else {
            return $this->fillOpacity;
        }
    }

    /**
     * setter for fillOpacity
     *
     * @param float $fillOpacity
     * @return void
     */
    public function setFillOpacity($fillOpacity)
    {
        $this->fillOpacity = (float)$fillOpacity;
    }
}

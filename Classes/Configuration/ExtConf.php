<?php
declare(strict_types = 1);
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
 * This class will streamline the values from extension manager configuration
 */
class ExtConf implements SingletonInterface
{
    /**
     * @var string
     */
    protected $mapProvider = '';

    /**
     * @var string
     */
    protected $defaultMapProvider = '';

    /**
     * @var string
     */
    protected $googleMapsLibrary = '';

    /**
     * @var string
     */
    protected $googleMapsJavaScriptApiKey = '';

    /**
     * @var string
     */
    protected $googleMapsGeocodeApiKey = '';

    /**
     * @var bool
     */
    protected $explicitAllowMapProviderRequests = false;

    /**
     * @var bool
     */
    protected $explicitAllowMapProviderRequestsBySessionOnly = false;

    /**
     * @var string
     */
    protected $defaultCountry = '';

    /**
     * @var float
     */
    protected $defaultLatitude;

    /**
     * @var float
     */
    protected $defaultLongitude;

    /**
     * @var int
     */
    protected $defaultRadius = 0;

    /**
     * @var string
     */
    protected $infoWindowContentTemplatePath = '';

    /**
     * @var string
     */
    protected $allowMapTemplatePath = '';

    /**
     * @var string
     */
    protected $strokeColor = '';

    /**
     * @var float
     */
    protected $strokeOpacity;

    /**
     * @var int
     */
    protected $strokeWeight = 0;

    /**
     * @var string
     */
    protected $fillColor = '';

    /**
     * @var float
     */
    protected $fillOpacity;

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

    public function __construct()
    {
        // On a fresh installation this value can be null.
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['maps2'])) {
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
    }

    /**
     * @return string
     */
    public function getMapProvider(): string
    {
        if (empty($this->mapProvider)) {
            $this->mapProvider = 'both';
        }

        return $this->mapProvider;
    }

    /**
     * @param string $mapProvider
     */
    public function setMapProvider(string $mapProvider)
    {
        $this->mapProvider = $mapProvider;
    }

    /**
     * @return string
     */
    public function getDefaultMapProvider(): string
    {
        if (empty($this->defaultMapProvider)) {
            $this->defaultMapProvider = 'google';
        }

        return $this->defaultMapProvider;
    }

    /**
     * @param string $defaultMapProvider
     */
    public function setDefaultMapProvider(string $defaultMapProvider)
    {
        $this->defaultMapProvider = $defaultMapProvider;
    }

    /**
     * @return string
     */
    public function getGoogleMapsLibrary(): string
    {
        if (trim($this->googleMapsLibrary) === '|') {
            $library = 'https://maps.googleapis.com/maps/api/js?key=|&libraries=places';
        } else {
            $library = $this->googleMapsLibrary;
        }

        if (!empty($library)) {
            // insert ApiKey
            $library = str_replace('|', $this->getGoogleMapsJavaScriptApiKey(), $library);
            // $parts: 0 = full string; 1 = s or empty; 2 = needed url
            if (preg_match('|^http(s)?://(.*)$|i', $library, $parts)) {
                return 'https://' . $parts[2];
            }
        }
        return '';
    }

    /**
     * @param string $googleMapsLibrary
     */
    public function setGoogleMapsLibrary(string $googleMapsLibrary)
    {
        $this->googleMapsLibrary = trim($googleMapsLibrary);
    }

    /**
     * @return string
     */
    public function getGoogleMapsJavaScriptApiKey(): string
    {
        return $this->googleMapsJavaScriptApiKey;
    }

    /**
     * @param string $googleMapsJavaScriptApiKey
     */
    public function setGoogleMapsJavaScriptApiKey(string $googleMapsJavaScriptApiKey)
    {
        $this->googleMapsJavaScriptApiKey = trim($googleMapsJavaScriptApiKey);
    }

    /**
     * @return string
     */
    public function getGoogleMapsGeocodeApiKey(): string
    {
        return $this->googleMapsGeocodeApiKey;
    }

    /**
     * @param string $googleMapsGeocodeApiKey
     */
    public function setGoogleMapsGeocodeApiKey(string $googleMapsGeocodeApiKey)
    {
        $this->googleMapsGeocodeApiKey = trim($googleMapsGeocodeApiKey);
    }

    /**
     * @return bool
     */
    public function getExplicitAllowMapProviderRequests(): bool
    {
        return $this->explicitAllowMapProviderRequests;
    }

    /**
     * @param bool $explicitAllowMapProviderRequests
     */
    public function setExplicitAllowMapProviderRequests($explicitAllowMapProviderRequests)
    {
        $this->explicitAllowMapProviderRequests = (bool)$explicitAllowMapProviderRequests;
    }

    /**
     * @return bool
     */
    public function getExplicitAllowMapProviderRequestsBySessionOnly(): bool
    {
        return $this->explicitAllowMapProviderRequestsBySessionOnly;
    }

    /**
     * @param bool $explicitAllowMapProviderRequestsBySessionOnly
     */
    public function setExplicitAllowMapProviderRequestsBySessionOnly($explicitAllowMapProviderRequestsBySessionOnly)
    {
        $this->explicitAllowMapProviderRequestsBySessionOnly = (bool)$explicitAllowMapProviderRequestsBySessionOnly;
    }

    /**
     * @return string
     */
    public function getDefaultCountry(): string
    {
        return $this->defaultCountry;
    }

    /**
     * @param string $defaultCountry
     */
    public function setDefaultCountry(string $defaultCountry)
    {
        $this->defaultCountry = trim($defaultCountry);
    }

    /**
     * @return float
     */
    public function getDefaultLatitude(): float
    {
        if (empty($this->defaultLatitude)) {
            return 0.00;
        } else {
            return $this->defaultLatitude;
        }
    }

    /**
     * @param float $defaultLatitude
     */
    public function setDefaultLatitude($defaultLatitude)
    {
        $this->defaultLatitude = (float)$defaultLatitude;
    }

    /**
     * @return float
     */
    public function getDefaultLongitude(): float
    {
        if (empty($this->defaultLongitude)) {
            return 0.00;
        } else {
            return $this->defaultLongitude;
        }
    }

    /**
     * @param float $defaultLongitude
     */
    public function setDefaultLongitude($defaultLongitude)
    {
        $this->defaultLongitude = (float)$defaultLongitude;
    }

    /**
     * @return int
     */
    public function getDefaultRadius(): int
    {
        if (empty($this->defaultRadius)) {
            return 250;
        } else {
            return $this->defaultRadius;
        }
    }

    /**
     * @param int $defaultRadius
     */
    public function setDefaultRadius($defaultRadius)
    {
        $this->defaultRadius = (int)$defaultRadius;
    }

    /**
     * @return string
     */
    public function getInfoWindowContentTemplatePath(): string
    {
        if (empty($this->infoWindowContentTemplatePath)) {
            $this->infoWindowContentTemplatePath = 'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html';
        }
        return $this->infoWindowContentTemplatePath;
    }

    /**
     * @param string $infoWindowContentTemplatePath
     */
    public function setInfoWindowContentTemplatePath(string $infoWindowContentTemplatePath)
    {
        $this->infoWindowContentTemplatePath = (string)$infoWindowContentTemplatePath;
    }

    /**
     * @return string
     */
    public function getAllowMapTemplatePath(): string
    {
        if (empty($this->allowMapTemplatePath)) {
            $this->allowMapTemplatePath = 'EXT:maps2/Resources/Private/Templates/AllowMapForm.html';
        }
        return $this->allowMapTemplatePath;
    }

    /**
     * @param string $allowMapTemplatePath
     */
    public function setAllowMapTemplatePath(string $allowMapTemplatePath)
    {
        $this->allowMapTemplatePath = (string)$allowMapTemplatePath;
    }

    /**
     * @return string
     */
    public function getStrokeColor(): string
    {
        if (empty($this->strokeColor)) {
            return '#FF0000';
        } else {
            return $this->strokeColor;
        }
    }

    /**
     * @param string $strokeColor
     */
    public function setStrokeColor(string $strokeColor)
    {
        $this->strokeColor = (string)$strokeColor;
    }

    /**
     * @return float
     */
    public function getStrokeOpacity(): float
    {
        if (empty($this->strokeOpacity)) {
            return 0.8;
        } else {
            return $this->strokeOpacity;
        }
    }

    /**
     * @param float $strokeOpacity
     */
    public function setStrokeOpacity($strokeOpacity)
    {
        $this->strokeOpacity = (float)$strokeOpacity;
    }

    /**
     * @return int
     */
    public function getStrokeWeight(): int
    {
        if (empty($this->strokeWeight)) {
            return 2;
        } else {
            return $this->strokeWeight;
        }
    }

    /**
     * @param int $strokeWeight
     */
    public function setStrokeWeight($strokeWeight)
    {
        $this->strokeWeight = (int)$strokeWeight;
    }

    /**
     * @return string
     */
    public function getFillColor(): string
    {
        if (empty($this->fillColor)) {
            return '#FF0000';
        } else {
            return $this->fillColor;
        }
    }

    /**
     * @param string $fillColor
     */
    public function setFillColor(string $fillColor)
    {
        $this->fillColor = (string)$fillColor;
    }

    /**
     * @return float
     */
    public function getFillOpacity(): float
    {
        if (empty($this->fillOpacity)) {
            return 0.35;
        } else {
            return $this->fillOpacity;
        }
    }

    /**
     * @param float $fillOpacity
     */
    public function setFillOpacity($fillOpacity)
    {
        $this->fillOpacity = (float)$fillOpacity;
    }

    /**
     * @return int
     */
    public function getMarkerIconWidth(): int
    {
        return $this->markerIconWidth;
    }

    /**
     * @param int $markerIconWidth
     */
    public function setMarkerIconWidth($markerIconWidth)
    {
        $this->markerIconWidth = (int)$markerIconWidth;
    }

    /**
     * @return int
     */
    public function getMarkerIconHeight(): int
    {
        return $this->markerIconHeight;
    }

    /**
     * @param int $markerIconHeight
     */
    public function setMarkerIconHeight($markerIconHeight)
    {
        $this->markerIconHeight = (int)$markerIconHeight;
    }

    /**
     * @return int
     */
    public function getMarkerIconAnchorPosX(): int
    {
        return $this->markerIconAnchorPosX;
    }

    /**
     * @param int $markerIconAnchorPosX
     */
    public function setMarkerIconAnchorPosX($markerIconAnchorPosX)
    {
        $this->markerIconAnchorPosX = (int)$markerIconAnchorPosX;
    }

    /**
     * @return int
     */
    public function getMarkerIconAnchorPosY(): int
    {
        return $this->markerIconAnchorPosY;
    }

    /**
     * @param int $markerIconAnchorPosY
     */
    public function setMarkerIconAnchorPosY($markerIconAnchorPosY)
    {
        $this->markerIconAnchorPosY = (int)$markerIconAnchorPosY;
    }
}

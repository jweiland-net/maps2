<?php
namespace JWeiland\Maps2\Client\Request;

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
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * An abstract class for all Google Maps Requests
 */
abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var string
     */
    protected $uri = '';

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * inject extConf
     *
     * @param ExtConf $extConf
     * @return void
     */
    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * Returns the uri
     *
     * @return string $uri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Sets the uri
     *
     * @param string $uri
     * @return void
     */
    public function setUri($uri)
    {
        $this->uri = (string)trim($uri);
    }

    /**
     * Returns the parameters
     *
     * @return array $parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets the parameters
     *
     * @param array $parameters
     * @return void
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = (array)$parameters;
    }

    /**
     * Adds a parameter
     *
     * @param string $parameter
     * @param mixed $value
     * @return void
     */
    public function addParameter($parameter, $value)
    {
        $this->parameters[$parameter] = $value;
    }

    /**
     * Gets a parameter
     *
     * @param string $parameter
     * @return mixed
     */
    public function getParameter($parameter)
    {
        return $this->parameters[$parameter];
    }

    /**
     * Check, if parameter exists
     *
     * @param string $parameter
     * @return bool
     */
    public function hasParameter($parameter)
    {
        return array_key_exists($parameter, $this->parameters);
    }

    /**
     * Prepare address for an uri
     * Further it will add some additional information like country
     *
     * @param string $address The address to update
     * @return string A prepared address which is valid for an uri
     */
    protected function updateAddressForUri($address)
    {
        // if address can be interpreted as zip, attach the default country to prevent a worldwide search
        if (
            MathUtility::canBeInterpretedAsInteger($address)
            && !empty($this->extConf->getDefaultCountry())
        ) {
            $address .= ' ' . $this->extConf->getDefaultCountry();
        }

        return rawurlencode($address);
    }

    /**
     * Check, if current Request is valid
     *
     * @return bool
     */
    public function isValidRequest()
    {
        $isValid = true;

        if (empty(trim($this->getUri()))) {
            $isValid = false;
        }

        return $isValid;
    }
}

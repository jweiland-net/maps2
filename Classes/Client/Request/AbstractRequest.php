<?php
declare(strict_types = 1);
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * An abstract class for all Requests to Map Providers
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

    public function __construct(ExtConf $extConf = null)
    {
        if ($extConf === null) {
            $extConf = GeneralUtility::makeInstance(ExtConf::class);
        }
        $this->extConf = $extConf;
    }

    /**
     * Returns the uri
     *
     * @return string $uri
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Sets the uri
     *
     * @param string $uri
     */
    public function setUri(string $uri)
    {
        $this->uri = trim($uri);
    }

    /**
     * Returns the parameters
     *
     * @return array $parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Sets the parameters
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Adds a parameter
     *
     * @param string $parameter
     * @param mixed $value
     */
    public function addParameter(string $parameter, $value)
    {
        $this->parameters[$parameter] = $value;
    }

    /**
     * Gets a parameter
     *
     * @param string $parameter
     * @return mixed
     */
    public function getParameter(string $parameter)
    {
        return $this->parameters[$parameter];
    }

    /**
     * Check, if parameter exists
     *
     * @param string $parameter
     * @return bool
     */
    public function hasParameter($parameter): bool
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
    protected function updateAddressForUri(string $address): string
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
    public function isValidRequest(): bool
    {
        $isValid = true;

        if (empty($this->getUri())) {
            $isValid = false;
        }

        if (!filter_var($this->getUri(), FILTER_VALIDATE_URL)) {
            $isValid = false;
        }

        return $isValid;
    }
}

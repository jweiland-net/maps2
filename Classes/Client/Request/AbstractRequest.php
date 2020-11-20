<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client\Request;

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
        $this->extConf = $extConf ?? GeneralUtility::makeInstance(ExtConf::class);
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri)
    {
        $this->uri = trim($uri);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $parameter
     * @param mixed $value
     */
    public function addParameter(string $parameter, $value)
    {
        $this->parameters[$parameter] = $value;
    }

    /**
     * @param string $parameter
     * @return mixed
     */
    public function getParameter(string $parameter)
    {
        return $this->parameters[$parameter];
    }

    public function hasParameter(string $parameter): bool
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

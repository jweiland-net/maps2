<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client\Request;

use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * An abstract class for all Requests to Map Providers
 */
abstract class AbstractRequest implements RequestInterface
{
    protected array $parameters = [];

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function addParameter(string $parameter, mixed $value): void
    {
        $this->parameters[$parameter] = $value;
    }

    public function getParameter(string $parameter): mixed
    {
        return $this->parameters[$parameter] ?? null;
    }

    public function hasParameter(string $parameter): bool
    {
        return array_key_exists($parameter, $this->parameters);
    }

    /**
     * Prepare address for a URI.
     * Further, it will add some additional information like country
     */
    protected function updateAddressForUri(string $address, string $defaultCountry): string
    {
        // if address can be interpreted as zip, attach the default country to prevent a worldwide search
        if (
            MathUtility::canBeInterpretedAsInteger($address)
            && $defaultCountry !== ''
        ) {
            $address .= ' ' . $defaultCountry;
        }

        return rawurlencode($address);
    }

    public function isValidRequest(): bool
    {
        $uri = $this->getUri();

        if ($uri === '') {
            return false;
        }

        return (bool)filter_var($uri, FILTER_VALIDATE_URL);
    }
}

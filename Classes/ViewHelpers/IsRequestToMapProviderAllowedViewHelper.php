<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers;

use JWeiland\Maps2\Helper\MapHelper;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Check, if selected map provider is allowed to be requested.
 */
final class IsRequestToMapProviderAllowedViewHelper extends AbstractConditionViewHelper
{
    public function __construct(
        private readonly MapHelper $mapHelper,
    ) {}

    /**
     * Convert all array and object types into a json string. Useful for data-Attributes
     */
    public function render(): bool
    {
        if (!$this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            throw new \RuntimeException('Required request not found in RenderingContext', 1780913191);
        }

        return $this->mapHelper->isRequestToMapProviderAllowed(
            $this->renderingContext->getAttribute(ServerRequestInterface::class),
        );
    }
}

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/*
 * Check, if selected map provider is allowed to be requested.
 */
class IsRequestToMapProviderAllowedViewHelper extends AbstractConditionViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Convert all array and object types into a json string. Useful for data-Attributes
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): bool {
        return GeneralUtility::makeInstance(MapHelper::class)
            ->isRequestToMapProviderAllowed();
    }

    protected static function getMapHelper(): MapHelper
    {
        return GeneralUtility::makeInstance(MapHelper::class);
    }
}

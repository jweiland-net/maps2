<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers;

use JWeiland\Maps2\Helper\SettingsHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/*
 * In overlay template we need a link to allow requests for map providers.
 * Use this ViewHelper to build that URI.
 */
class RequestUriForOverlayViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'ttContentUid',
            'int',
            'The tt_content UID of the record which shows the map',
            false,
            0
        );
    }

    /**
     * Convert all array and object types into a json string. Useful for data-Attributes
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        // Method setAddQueryStringMethod is deprecated with TYPO3 11. Remove while removing TYPO3 10 compatibility
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class)
            ->reset()
            ->setAddQueryString(true)
            ->setAddQueryStringMethod('GET')
            ->setArguments([
                'tx_maps2_maps2' => [
                    'mapProviderRequestsAllowedForMaps2' => 1,
                ],
            ])
            ->setArgumentsToBeExcludedFromQueryString(['cHash']);

        if ((self::getSettingsHelper()->getPreparedSettings()['overlay']['link']['addSection'] ?? '') === '1') {
            $ttContentUid = (int)($arguments['ttContentUid'] ?? 0);
            if ($ttContentUid) {
                $uriBuilder->setSection('c' . $ttContentUid);
            }
        }

        return $uriBuilder->build();
    }

    protected static function getSettingsHelper(): SettingsHelper
    {
        return GeneralUtility::makeInstance(SettingsHelper::class);
    }
}

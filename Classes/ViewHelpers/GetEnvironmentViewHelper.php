<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\SettingsHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * If you make use of our Partial/PoiCollection or Partial/EditPoiCollection we need some additional information
 * about current record and Extension Settings of maps2. Use this VH to add these additional variables to template.
 */
class GetEnvironmentViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Convert all array and object types into a json string. Useful for data-Attributes
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $templateVariableContainer = $renderingContext->getVariableProvider();
        $extbaseRequest = self::getExtbaseRequest($renderingContext);
        if (!$extbaseRequest instanceof RequestInterface) {
            return '';
        }

        $templateVariableContainer->add(
            'environment',
            [
                'settings' => self::getSettingsHelper()->getPreparedSettings(),
                'extConf' => ObjectAccess::getGettableProperties(self::getExtConf()),
                'id' => (int)($extbaseRequest->getQueryParams()['id'] ?? 0),
                'contentRecord' => $extbaseRequest->getAttribute('currentContentObject')->data,
            ]
        );

        $content = $renderChildrenClosure();

        $templateVariableContainer->remove('environment');

        return $content;
    }

    private static function getExtbaseRequest(RenderingContextInterface $renderingContext): ?RequestInterface
    {
        if (
            $renderingContext instanceof RenderingContext
            && $renderingContext->getRequest() instanceof RequestInterface
        ) {
            return $renderingContext->getRequest();
        }

        return null;
    }

    protected static function getSettingsHelper(): SettingsHelper
    {
        return GeneralUtility::makeInstance(SettingsHelper::class);
    }

    protected static function getExtConf(): ExtConf
    {
        return GeneralUtility::makeInstance(ExtConf::class);
    }
}

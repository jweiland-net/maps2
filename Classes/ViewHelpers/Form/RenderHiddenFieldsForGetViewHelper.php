<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers\Form;

use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * This ViewHelper is useful to render special hidden fields
 * to save and modify markers in frontend of foreign extensions
 */
class RenderHiddenFieldsForGetViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'pageUid',
            'int',
            'The target page UID',
        );

        $this->registerArgument(
            'action',
            'string',
            'Target action',
        );

        $this->registerArgument(
            'controller',
            'string',
            'Target controller. If null current controllerName is used',
        );
    }

    /**
     * Checks if caching framework has the requested cache entry
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
    ): string {
        $extensionService = GeneralUtility::makeInstance(ExtensionService::class);
        $cacheHashCalculator = GeneralUtility::makeInstance(CacheHashCalculator::class);
        $extbaseRequest = self::getExtbaseRequest($renderingContext);
        if (!$extbaseRequest instanceof RequestInterface) {
            return '';
        }

        $pluginNamespace = $extensionService->getPluginNamespace(
            $extbaseRequest->getControllerExtensionName(),
            $extbaseRequest->getPluginName(),
        );

        // get pageUid
        $pageUid = (int)$arguments['pageUid'];
        if ($pageUid === 0) {
            $pageArguments = $extbaseRequest->getAttribute('routing');
            if ($pageArguments instanceof PageArguments) {
                $pageUid = $pageArguments->getPageId();
            }
        }

        // create array for cHash calculation
        $parameters = [];
        $parameters['id'] = $pageUid;
        $parameters[$pluginNamespace]['controller'] = $arguments['controller'];
        $parameters[$pluginNamespace]['action'] = $arguments['action'];
        $cacheHashArray = $cacheHashCalculator->getRelevantParameters(
            GeneralUtility::implodeArrayForUrl('', $parameters),
        );

        // create array of hidden fields for GET forms
        $fields = [];
        $fields[] = sprintf(
            '<input type="hidden" name="id" value="%d" />',
            $pageUid,
        );
        $fields[] = sprintf(
            '<input type="hidden" name="%s[controller]" value="%s" />',
            $pluginNamespace,
            $arguments['controller'],
        );
        $fields[] = sprintf(
            '<input type="hidden" name="%s[action]" value="%s" />',
            $pluginNamespace,
            $arguments['action'],
        );

        // add cHash
        $fields[] = sprintf(
            '<input type="hidden" name="cHash" value="%s" />',
            $cacheHashCalculator->calculateCacheHash(
                $cacheHashArray,
            ),
        );

        return implode(chr(10), $fields);
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
}

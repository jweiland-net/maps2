<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers\Form;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ExtensionService;
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
        $this->registerArgument('pageUid', 'int', 'The target page UID', false, null);
        $this->registerArgument('action', 'string', 'Target action', false, null);
        $this->registerArgument('controller', 'string', 'Target controller. If null current controllerName is used', false, null);
    }

    /**
     * Checks if caching framework has the requested cache entry
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $extensionService = $objectManager->get(ExtensionService::class);
        $cacheHashCalculator = $objectManager->get(CacheHashCalculator::class);

        $pluginNamespace = $extensionService->getPluginNamespace(
            $renderingContext->getControllerContext()->getRequest()->getControllerExtensionName(),
            $renderingContext->getControllerContext()->getRequest()->getPluginName()
        );

        // get pageUid
        $pageUid = $arguments['pageUid'] ?: $GLOBALS['TSFE']->id;

        // create array for cHash calculation
        $parameters = [];
        $parameters['id'] = $pageUid;
        $parameters[$pluginNamespace]['controller'] = $arguments['controller'];
        $parameters[$pluginNamespace]['action'] = $arguments['action'];
        $cacheHashArray = $cacheHashCalculator->getRelevantParameters(
            GeneralUtility::implodeArrayForUrl('', $parameters)
        );

        // create array of hidden fields for GET forms
        $fields = [];
        $fields[] = sprintf(
            '<input type="hidden" name="id" value="%d" />',
            $pageUid
        );
        $fields[] = sprintf(
            '<input type="hidden" name="%s[controller]" value="%s" />',
            $pluginNamespace,
            $arguments['controller']
        );
        $fields[] = sprintf(
            '<input type="hidden" name="%s[action]" value="%s" />',
            $pluginNamespace,
            $arguments['action']
        );

        // add cHash
        $fields[] = sprintf(
            '<input type="hidden" name="cHash" value="%s" />',
            $cacheHashCalculator->calculateCacheHash(
                $cacheHashArray
            )
        );

        return implode(chr(10), $fields);
    }
}

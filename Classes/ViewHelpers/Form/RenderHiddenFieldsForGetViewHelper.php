<?php
namespace JWeiland\Maps2\ViewHelpers\Form;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This ViewHelper is useful to render special hidden fields
 * to save and modify markers in frontend of foreign extensions
 */
class RenderHiddenFieldsForGetViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var ExtensionService
     */
    protected $extensionService;

    /**
     * @var CacheHashCalculator
     */
    protected $cacheHashCalculator;

    /**
     * inject extensionService
     *
     * @param ExtensionService $extensionService
     */
    public function injectExtensionService(ExtensionService $extensionService)
    {
        $this->extensionService = $extensionService;
    }

    /**
     * inject cacheHashCalculator
     *
     * @param CacheHashCalculator $cacheHashCalculator
     */
    public function injectCacheHashCalculator(CacheHashCalculator $cacheHashCalculator)
    {
        $this->cacheHashCalculator = $cacheHashCalculator;
    }

    /**
     * Initialize ViewHelper arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('pageUid', 'int', 'The target page UID', false, null);
        $this->registerArgument('action', 'string', 'Target action', false, null);
        $this->registerArgument('controller', 'string', 'Target controller. If null current controllerName is used', false, null);
    }

    /**
     * Implements a ViewHelper to trim explode comma separated strings
     *
     * @return array
     */
    public function render()
    {
        $pluginNamespace = $this->extensionService->getPluginNamespace(
            $this->renderingContext->getControllerContext()->getRequest()->getControllerExtensionName(),
            $this->renderingContext->getControllerContext()->getRequest()->getPluginName()
        );
        // get pageUid
        $pageUid = $this->arguments['pageUid'] ?: $GLOBALS['TSFE']->id;

        // create array for cHash calculation
        $parameters = [];
        $parameters['id'] = $pageUid;
        $parameters[$pluginNamespace]['controller'] = $this->arguments['controller'];
        $parameters[$pluginNamespace]['action'] = $this->arguments['action'];
        $cacheHashArray = $this->cacheHashCalculator->getRelevantParameters(
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
            $this->arguments['controller']
        );
        $fields[] = sprintf(
            '<input type="hidden" name="%s[action]" value="%s" />',
            $pluginNamespace,
            $this->arguments['action']
        );

        // add cHash
        $fields[] = sprintf(
            '<input type="hidden" name="cHash" value="%s" />',
            $this->cacheHashCalculator->calculateCacheHash(
                $cacheHashArray
            )
        );

        return implode(chr(10), $fields);
    }
}

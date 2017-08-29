<?php
namespace JWeiland\Maps2\ViewHelpers\Widget\Controller;

/**
 * This file is part of the TYPO3 CMS project.
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
use JWeiland\Maps2\Domain\Model\PoiCollection;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\Widget\WidgetRequest;
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;

/**
 * Class EditPoiController
 *
 * @category ViewHelpers/Widget/Controller
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class EditPoiController extends AbstractController
{
    /**
     * @var RenderingContext
     */
    protected $renderingContext;

    /**
     * initializes all actions
     *
     * @return void
     */
    public function initializeAction()
    {
        /** @var WidgetRequest $widgetRequest */
        $widgetRequest = $this->request;
        $this->renderingContext = $widgetRequest->getWidgetContext()->getViewHelperChildNodeRenderingContext();
    }

    /**
     * initialize view
     * add some global vars to view
     *
     * @param ViewInterface $view
     *
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {
        ArrayUtility::mergeRecursiveWithOverrule($this->defaultSettings, $this->settings);
        $this->assign('data', $this->configurationManager->getContentObject()->data);
        $this->assign('environment', array(
            'settings' => $this->defaultSettings,
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'id' => $GLOBALS['TSFE']->id,
            'contentRecord' => $this->configurationManager->getContentObject()->data
        ));
    }

    /**
     * index action
     *
     * @return string
     */
    public function indexAction()
    {
        $poiCollection = $this->widgetConfiguration['poiCollection'];
        if ($poiCollection instanceof PoiCollection) {
            $poiCollection->setInfoWindowContent($this->renderInfoWindow($poiCollection));
        } else {
            // this is more a fallback. It would be better that the foreign extension author generates a PoiCollection on its own
            /** @var PoiCollection $poiCollection */
            $poiCollection = $this->objectManager->get('JWeiland\\Maps2\\Domain\\Model\\PoiCollection');
            $poiCollection->setTitle('Temporary Fallback');
            $poiCollection->setLatitude($this->extConf->getDefaultLatitude());
            $poiCollection->setLongitude($this->extConf->getDefaultLongitude());
            $poiCollection->setCollectionType('Point');
        }
        $this->assign('poiCollection', $poiCollection);
        $this->assign('override', $this->widgetConfiguration['override']);
        $this->assign('property', $this->widgetConfiguration['property']);

        return $this->getParsingState()->render($this->renderingContext);
    }

    /**
     * get parsing state
     *
     * @return ParsedTemplateInterface|\TYPO3\CMS\Fluid\Core\Parser\ParsedTemplateInterface
     */
    protected function getParsingState()
    {
        $templateSource = file_get_contents(sprintf(
            '%sResources/Private/Templates/ViewHelpers/Widget/EditPoi/Index.html',
            ExtensionManagementUtility::extPath('maps2')
        ));

        if (version_compare(TYPO3_branch, '8.7') >= 0) {
            /** @var TemplateParser $templateParser */
            $templateParser = $this->objectManager->get('TYPO3Fluid\\Fluid\\Core\\Parser\\TemplateParser');
            return $templateParser->parse($templateSource);
        } else {
            /** @var \TYPO3\CMS\Fluid\Core\Parser\TemplateParser $templateParser */
            $templateParser = $this->objectManager->get('TYPO3\\CMS\\Fluid\\Core\\Parser\\TemplateParser');
            return $templateParser->parse($templateSource);
        }
    }

    /**
     * Assign a value to the variable container.
     *
     * @param string $key The key of a view variable to set
     * @param mixed $value The value of the view variable
     *
     * @return void
     */
    public function assign($key, $value) {
        $templateVariableContainer = $this->renderingContext->getTemplateVariableContainer();
        if ($templateVariableContainer->exists($key)) {
            $templateVariableContainer->remove($key);
        }
        $templateVariableContainer->add($key, $value);
    }
}

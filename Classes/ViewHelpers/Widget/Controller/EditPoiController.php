<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers\Widget\Controller;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;

/**
 * An Edit controller for foreign extension authors
 * to show Google Maps with a drag- and drop-able marker
 */
class EditPoiController extends AbstractController
{
    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    public function indexAction()
    {
        $poiCollection = $this->widgetConfiguration['poiCollection'];

        // This is more a fallback. It would be better, if the foreign extension author generates a PoiCollection on its own
        if (!$poiCollection instanceof PoiCollection) {
            /** @var PoiCollection $poiCollection */
            $poiCollection = $this->objectManager->get(PoiCollection::class);
            $poiCollection->setTitle('Temporary Fallback');
            $poiCollection->setLatitude($this->extConf->getDefaultLatitude());
            $poiCollection->setLongitude($this->extConf->getDefaultLongitude());
            $poiCollection->setCollectionType('Point');
        }

        $this->view->assign('poiCollection', $poiCollection);
        $this->view->assign('title', $this->widgetConfiguration['title']);
        $this->view->assign('override', $this->widgetConfiguration['override']);
        $this->view->assign('property', $this->widgetConfiguration['property']);
    }

    /**
     * This method will be and must be called from EditPoiViewHelper
     * to have access to the previous ViewHelperVariableContainer where
     * f:form stores its context.
     *
     * @param RenderingContextInterface $renderingContext
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext)
    {
        $this->renderingContext = $renderingContext;
    }

    /**
     * Set RenderingContext to current Template to have access to previous ViewHelperVariableContainer
     *
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     */
    protected function setViewConfiguration(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view)
    {
        if ($view instanceof AbstractTemplateView) {
            $view->getRenderingContext()->setViewHelperVariableContainer(
                $this->renderingContext->getViewHelperVariableContainer()
            );
        }
    }
}

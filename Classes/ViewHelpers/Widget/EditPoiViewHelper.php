<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers\Widget;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Service\MapProviderRequestService;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper;

/**
 * As an extension developer you can use this ViewHelper
 * to show Google Maps within your extension.
 * Further the user can drag and drop the marker
 */
class EditPoiViewHelper extends AbstractWidgetViewHelper
{
    /**
     * @var Controller\EditPoiController
     */
    protected $controller;

    /**
     * @var MapProviderRequestService
     */
    protected $mapProviderRequestService;

    /**
     * @var MapService
     */
    protected $mapService;

    public function injectController(Controller\EditPoiController $controller)
    {
        $this->controller = $controller;
    }

    public function injectMapProviderRequestService(MapProviderRequestService $mapProviderRequestService)
    {
        $this->mapProviderRequestService = $mapProviderRequestService;
    }

    public function injectMapService(MapService $mapService)
    {
        $this->mapService = $mapService;
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'title',
            'string',
            'As title is required in PoiCollection, you have to assign a title here, too',
            true
        );
        $this->registerArgument(
            'override',
            'array',
            'Here you can override default settings individually',
            false,
            []
        );
        $this->registerArgument(
            'poiCollection',
            PoiCollection::class,
            'The poiColleciton object to render',
            true
        );
        $this->registerArgument(
            'property',
            'string',
            'Property where to save the new PoiCollection record',
            false,
            'txMapsUid'
        );
    }

    /**
     * @return string
     */
    public function render()
    {
        // Set previous RenderingContext to this VH, to have access to ViewHelperVariableContainer
        $this->controller->setRenderingContext($this->renderingContext);

        if (!$this->mapProviderRequestService->isRequestToMapProviderAllowed()) {
            return $this->mapService->showAllowMapForm();
        }

        return $this->initiateSubRequest();
    }
}

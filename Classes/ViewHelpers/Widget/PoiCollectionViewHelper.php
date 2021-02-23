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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper;

/**
 * As an extension developer you can use this ViewHelper
 * to show Google Maps within your extension.
 */
class PoiCollectionViewHelper extends AbstractWidgetViewHelper
{
    /**
     * @var Controller\PoiCollectionController
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

    public function injectController(Controller\PoiCollectionController $controller)
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
            'poiCollection',
            PoiCollection::class,
            'The poiCollection object to render'
        );
        $this->registerArgument(
            'poiCollections',
            \Traversable::class,
            'The poiCollection objects as array to render'
        );
        $this->registerArgument(
            'override',
            'array',
            'Here you can override default settings individually',
            false,
            []
        );
    }

    /**
     * Render the widget
     *
     * @return string
     */
    public function render()
    {
        if (!$this->mapProviderRequestService->isRequestToMapProviderAllowed()) {
            return $this->mapService->showAllowMapForm();
        }

        return $this->initiateSubRequest();
    }
}

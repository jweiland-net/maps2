<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\ViewHelpers\Widget;

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
     * @param Controller\PoiCollectionController $controller
     */
    public function injectController(Controller\PoiCollectionController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Initialize all arguments.
     */
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
        $mapProviderRequestService = GeneralUtility::makeInstance(MapProviderRequestService::class);
        if (!$mapProviderRequestService->isRequestToMapProviderAllowed()) {
            $mapService = GeneralUtility::makeInstance(MapService::class);
            return $mapService->showAllowMapForm();
        }

        return $this->initiateSubRequest();
    }
}

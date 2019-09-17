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
 * Further the user can drag and drop the marker
 */
class EditPoiViewHelper extends AbstractWidgetViewHelper
{
    /**
     * @var Controller\EditPoiController
     */
    protected $controller;

    /**
     * @param Controller\EditPoiController $controller
     */
    public function injectController(Controller\EditPoiController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Initialize all arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
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
            false, 'txMapsUid'
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

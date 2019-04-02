<?php
namespace JWeiland\Maps2\ViewHelpers\Widget\Controller;

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

/**
 * An Edit controller for foreign extension authors
 * to show Google Maps with a drag- and drop-able marker
 */
class EditPoiController extends AbstractController
{
    /**
     * index action
     */
    public function indexAction()
    {
        $poiCollection = $this->widgetConfiguration['poiCollection'];
        if ($poiCollection instanceof PoiCollection) {
            $this->mapService->setInfoWindow($poiCollection);
        } else {
            // this is more a fallback. It would be better that the foreign extension author generates a PoiCollection on its own
            /** @var PoiCollection $poiCollection */
            $poiCollection = $this->objectManager->get(PoiCollection::class);
            $poiCollection->setTitle('Temporary Fallback');
            $poiCollection->setLatitude($this->extConf->getDefaultLatitude());
            $poiCollection->setLongitude($this->extConf->getDefaultLongitude());
            $poiCollection->setCollectionType('Point');
        }
        $this->view->assign('poiCollection', $poiCollection);
        $this->view->assign('override', $this->widgetConfiguration['override']);
        $this->view->assign('property', $this->widgetConfiguration['property']);
    }
}

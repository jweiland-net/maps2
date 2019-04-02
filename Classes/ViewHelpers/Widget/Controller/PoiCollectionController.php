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
 * A show controller for foreign extension authors
 * to show Google Maps with a fixed poi collection record
 */
class PoiCollectionController extends AbstractController
{
    /**
     * index action
     */
    public function indexAction()
    {
        $poiCollection = $this->widgetConfiguration['poiCollection'];
        if ($poiCollection instanceof PoiCollection) {
            $this->mapService->setInfoWindow($poiCollection);
            $this->view->assign('poiCollections', [$poiCollection]);
        } elseif ($this->widgetConfiguration['poiCollections'] instanceof \Traversable) {
            /** @var PoiCollection $poiCollection */
            foreach ($this->widgetConfiguration['poiCollections'] as $poiCollection) {
                $this->mapService->setInfoWindow($poiCollection);
            }
            $this->view->assign('poiCollections', $this->widgetConfiguration['poiCollections']);
        }
        $this->view->assign('override', $this->widgetConfiguration['override']);
    }
}

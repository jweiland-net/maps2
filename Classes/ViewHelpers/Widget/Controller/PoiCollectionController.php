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

/**
 * A show controller for foreign extension authors
 * to show Google Maps with a fixed poi collection record
 */
class PoiCollectionController extends AbstractController
{
    public function indexAction()
    {
        $poiCollection = $this->widgetConfiguration['poiCollection'];
        if ($poiCollection instanceof PoiCollection) {
            $this->view->assign('poiCollections', [$poiCollection]);
        } elseif ($this->widgetConfiguration['poiCollections'] instanceof \Traversable) {
            $this->view->assign('poiCollections', $this->widgetConfiguration['poiCollections']);
        }
        $this->view->assign('override', $this->widgetConfiguration['override']);
    }
}

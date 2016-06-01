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
     * index action
     *
     * @return string
     */
    public function indexAction()
    {
        $poiCollection = $this->widgetConfiguration['poiCollection'];
        if ($poiCollection instanceof PoiCollection) {
            $poiCollection->setInfoWindowContent($this->renderInfoWindow($poiCollection));
            $this->view->assign('poiCollection', $poiCollection);
            $this->view->assign('poiCollections', $this->getPoiCollectionsAsJson(array($poiCollection)));
        }
        $this->view->assign('override', $this->widgetConfiguration['override']);
    }
}

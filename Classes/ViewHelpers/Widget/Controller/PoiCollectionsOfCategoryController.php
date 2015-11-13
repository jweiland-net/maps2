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
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class PoiCollectionsOfCategoryController
 *
 * @category ViewHelpers/Widget/Controller
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class PoiCollectionsOfCategoryController extends AbstractController
{

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected $poiCollections;

    /**
     * initializes the index action
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->poiCollections = $this->widgetConfiguration['poiCollections'];
    }

    /**
     * index action
     *
     * @return string
     */
    public function indexAction()
    {
        $this->view->assign('extConf', ObjectAccess::getGettableProperties($this->extConf));
        $this->view->assign('poiCollections', $this->poiCollections);
    }
}

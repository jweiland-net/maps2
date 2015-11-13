<?php
namespace JWeiland\Maps2\ViewHelpers\Widget;

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
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper;

/**
 * Class PoiCollectionsOfCategoryViewHelper
 *
 * @category ViewHelpers/Widget
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class PoiCollectionsOfCategoryViewHelper extends AbstractWidgetViewHelper
{

    /**
     * @var \JWeiland\Maps2\ViewHelpers\Widget\Controller\PoiCollectionsOfCategoryController
     */
    protected $controller;

    /**
     * inject controller
     *
     * @param \JWeiland\Maps2\ViewHelpers\Widget\Controller\PoiCollectionsOfCategoryController $controller
     * @return void
     */
    public function injectController(
        \JWeiland\Maps2\ViewHelpers\Widget\Controller\PoiCollectionsOfCategoryController $controller
    ) {
        $this->controller = $controller;
    }

    /**
     *
     * @param \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $poiCollections
     * @return string
     */
    public function render(\TYPO3\CMS\Extbase\Persistence\QueryResultInterface $poiCollections)
    {
        return $this->initiateSubRequest();
    }
}

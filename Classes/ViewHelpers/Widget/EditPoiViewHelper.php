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
 * Class EditPoiViewHelper
 *
 * @category ViewHelpers/Widget
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class EditPoiViewHelper extends AbstractWidgetViewHelper
{

    /**
     * @var \JWeiland\Maps2\ViewHelpers\Widget\Controller\EditPoiController
     */
    protected $controller;

    /**
     * inject controller
     *
     * @param \JWeiland\Maps2\ViewHelpers\Widget\Controller\EditPoiController $controller
     * @return void
     */
    public function injectController(\JWeiland\Maps2\ViewHelpers\Widget\Controller\EditPoiController $controller)
    {
        $this->controller = $controller;
    }

    /**
     *
     * @param \JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection
     * @param integer $width Width of the map
     * @param integer $height Height of the map
     * @param string $prepend Extension/Plugin combination to identify GET/POST vars: tx_yellowpages2_directory
     * @param string $id This is the id-Attribute of the div, where to show the map. Defaults to "maps2" if not given. This is good to display multiple maps on one page.
     * @param array $mapOptions Google Map Options
     * @return string
     */
    public function render(\JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection, $width = 400, $height = 300, $prepend = '', $id = 'maps2', array $mapOptions = array())
    {
        return $this->initiateSubRequest();
    }
}

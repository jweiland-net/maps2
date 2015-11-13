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
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Class PointController
 *
 * @category ViewHelpers/Widget/Controller
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class PointController extends AbstractController
{

    /**
     * @var array
     */
    protected $center = array();

    /**
     * @var array
     */
    protected $mapOptions = array(
        'zoom' => 12,
        'mapTypeId' => 'google.maps.MapTypeId.ROADMAP',
        'panControl' => 1,
        'zoomControl' => 1,
        'mapTypeControl' => 1,
        'scaleControl' => 1,
        'streetViewControl' => 1,
        'overviewMapControl' => 1,
    );

    /**
     * @var int
     */
    protected $width = 400;

    /**
     * @var int
     */
    protected $height = 300;

    /**
     * initializes the index action
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->center = array(
            'latitude' => $this->extConf->getDefaultLatitude(),
            'longitude' => $this->extConf->getDefaultLongitude()
        );
        ArrayUtility::mergeRecursiveWithOverrule($this->center, $this->widgetConfiguration['center'], true);
        ArrayUtility::mergeRecursiveWithOverrule($this->mapOptions, $this->getMapOptions(), true);
        $this->width = $this->widgetConfiguration['width'];
        $this->height = $this->widgetConfiguration['height'];
    }

    /**
     * index action
     *
     * @return string
     */
    public function indexAction()
    {
        $this->view->assign('center', $this->center);
        $this->view->assign('mapOptions', $this->mapOptions);
        $this->view->assign('width', $this->width);
        $this->view->assign('height', $this->height);
    }

    /**
     * if some values are set to false in template, they were set to null
     * This method returns this values back to false
     *
     * @return array
     */
    public function getMapOptions()
    {
        foreach ($this->widgetConfiguration['mapOptions'] as $key => $value) {
            if (empty($this->widgetConfiguration['mapOptions'][$key])) {
                $this->widgetConfiguration['mapOptions'][$key] = 0;
            } else {
                $this->widgetConfiguration['mapOptions'][$key] = 1;
            }
        }
        return $this->widgetConfiguration['mapOptions'];
    }
}

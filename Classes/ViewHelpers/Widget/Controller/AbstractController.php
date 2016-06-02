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
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController;

/**
 * Class AbstractController
 *
 * @category ViewHelpers/Widget/Controller
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
abstract class AbstractController extends AbstractWidgetController
{
    /**
     * @var array
     */
    protected $defaultSettings = array(
        'zoom' => 10,
        'panControl' => 1,
        'zoomControl' => 1,
        'mapTypeControl' => 1,
        'scaleControl' => 1,
        'streetViewControl' => 1,
        'overviewMapControl' => 1,
        'mapTypeId' => 'google.maps.MapTypeId.ROADMAP'
    );

    /**
     * @var \JWeiland\Maps2\Configuration\ExtConf
     */
    protected $extConf = null;

    /**
     * inject extConf
     *
     * @param \JWeiland\Maps2\Configuration\ExtConf $extConf
     * @return void
     */
    public function injectExtConf(\JWeiland\Maps2\Configuration\ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * initialize view
     * add some global vars to view
     *
     * @return void
     */
    public function initializeView()
    {
        ArrayUtility::mergeRecursiveWithOverrule($this->defaultSettings, $this->settings);
        $this->view->assign('data', $this->configurationManager->getContentObject()->data);
        $this->view->assign('environment', json_encode(array(
            'settings' => $this->defaultSettings,
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'id' => $GLOBALS['TSFE']->id,
            'contentRecord' => $this->configurationManager->getContentObject()->data
        )));
    }

    /**
     * Render InfoWindow for marker
     *
     * @param PoiCollection $poiCollection
     * @return string
     */
    protected function renderInfoWindow(PoiCollection $poiCollection)
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->assign('poiCollection', $poiCollection);
        $view->setTemplatePathAndFilename(
            GeneralUtility::getFileAbsFileName(
                'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html'
            )
        );
        return $view->render();
    }
}

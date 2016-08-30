<?php
namespace JWeiland\Maps2\Controller;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class AbstractController
 *
 * @category Controller
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class AbstractController extends ActionController
{
    /**
     * @var \JWeiland\Maps2\Configuration\ExtConf
     */
    protected $extConf;

    /**
     * @var \JWeiland\Maps2\Utility\DataMapper
     */
    protected $dataMapper;

    /**
     * @var \JWeiland\Maps2\Utility\GeocodeUtility
     */
    protected $geocodeUtility;

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
     * inject dataMapper
     *
     * @param \JWeiland\Maps2\Utility\DataMapper $dataMapper
     * @return void
     */
    public function injectDataMapper(\JWeiland\Maps2\Utility\DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * inject geocodeUtility
     *
     * @param \JWeiland\Maps2\Utility\GeocodeUtility $geocodeUtility
     * @return void
     */
    public function injectGeocodeUtility(\JWeiland\Maps2\Utility\GeocodeUtility $geocodeUtility)
    {
        $this->geocodeUtility = $geocodeUtility;
    }
    
    /**
     * Initializes the controller before invoking an action method.
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->settings['infoWindowContentTemplatePath'] = trim($this->settings['infoWindowContentTemplatePath']);
    }

    /**
     * initialize view
     * add some global vars to view
     *
     * @param ViewInterface $view The view to be initialized
     *
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {
        $view->assign('data', $this->configurationManager->getContentObject()->data);
        $view->assign('environment', array(
            'settings' => $this->settings,
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'id' => $GLOBALS['TSFE']->id,
            'contentRecord' => $this->configurationManager->getContentObject()->data
        ));
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
                $this->getInfoWindowContentTemplatePath()
            )
        );
        return $view->render();
    }
    
    /**
     * Get template path for info window content
     *
     * @return string
     */
    protected function getInfoWindowContentTemplatePath()
    {
        // get default template path
        $path = $this->extConf->getInfoWindowContentTemplatePath();
        if (
            isset($this->settings['infoWindowContentTemplatePath']) &&
            !empty($this->settings['infoWindowContentTemplatePath'])
        ) {
            $path = $this->settings['infoWindowContentTemplatePath'];
        }
        
        return $path;
    }
}

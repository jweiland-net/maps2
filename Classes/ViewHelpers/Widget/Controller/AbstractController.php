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

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
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
    protected $defaultSettings = [
        'zoom' => 10,
        'zoomControl' => true,
        'mapTypeControl' => true,
        'scaleControl' => true,
        'streetViewControl' => true,
        'fullscreenMapControl' => true,
        'mapTypeId' => 'google.maps.MapTypeId.ROADMAP'
    ];

    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var MapService
     */
    protected $mapService;

    /**
     * inject extConf
     *
     * @param ExtConf $extConf
     *
     * @return void
     */
    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * inject mapService
     *
     * @param MapService $mapService
     *
     * @return void
     */
    public function injectMapService(MapService $mapService)
    {
        $this->mapService = $mapService;
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
     * @param ViewInterface $view
     *
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {
        ArrayUtility::mergeRecursiveWithOverrule($this->defaultSettings, $this->getMaps2TypoScriptSettings());
        $view->assign('data', $this->configurationManager->getContentObject()->data);
        $view->assign('environment', [
            'settings' => $this->defaultSettings,
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'id' => $GLOBALS['TSFE']->id,
            'contentRecord' => $this->configurationManager->getContentObject()->data
        ]);
    }

    /**
     * Get TypoScript settings of maps2
     *
     * @return array
     */
    protected function getMaps2TypoScriptSettings()
    {
        $fullTypoScript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $settings = ArrayUtility::getValueByPath($fullTypoScript, 'plugin./tx_maps2.');
        return is_array($settings) ? $settings : [];
    }
}

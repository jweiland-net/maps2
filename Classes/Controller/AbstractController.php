<?php
namespace JWeiland\Maps2\Controller;

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
use JWeiland\Maps2\Utility\DataMapper;
use JWeiland\Maps2\Utility\GeocodeUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class AbstractController
 *
 * @category Controller
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class AbstractController extends ActionController
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var DataMapper
     */
    protected $dataMapper;

    /**
     * @var GeocodeUtility
     */
    protected $geocodeUtility;

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
     * inject dataMapper
     *
     * @param DataMapper $dataMapper
     *
     * @return void
     */
    public function injectDataMapper(DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * inject geocodeUtility
     *
     * @param GeocodeUtility $geocodeUtility
     *
     * @return void
     */
    public function injectGeocodeUtility(GeocodeUtility $geocodeUtility)
    {
        $this->geocodeUtility = $geocodeUtility;
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
        $this->throwExceptionIfJavaScriptApiKeyIsNotSet();
        $this->settings['infoWindowContentTemplatePath'] = trim($this->settings['infoWindowContentTemplatePath']);
    }

    /**
     * Throw exception if ApiKey is not in JS file path
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function throwExceptionIfJavaScriptApiKeyIsNotSet()
    {
        $tsWithPageObjects = $this->getTypoScriptWithPageObjects();
        switch (count($tsWithPageObjects)) {
            case 0:
                throw new \Exception(
                    'There is no PAGE object in TypoScript configured with typeNum = 0. Please create one first, before using maps2',
                    1524222457
                );
                break;
            case 1:
                // fine. Now check against included JS
                $tsWithPageObject = current($tsWithPageObjects);
                $validTypoScriptPaths = array_intersect_key(
                    $tsWithPageObject,
                    [
                        'includeJS.' => true,
                        'includeJSFooter.' => true,
                        'includeJSFooterLibs.' => true,
                        'includeJSLibs.' => true
                    ]
                );
                $jsLibraryHasApiKey = false;
                foreach ($validTypoScriptPaths as $path => $jsLibraries) {
                    foreach ($jsLibraries as $name => $source) {
                        if (!is_string($source)) {
                            continue;
                        }
                        $query = parse_url($source, PHP_URL_QUERY);
                        if (!empty($query)) {
                            parse_str($query, $parameters);
                            if (isset($parameters['key']) && !empty($parameters['key'])) {
                                $jsLibraryHasApiKey = true;
                                break 2;
                            }
                        }
                    }
                }
                break;
            default:
                throw new \Exception(
                    'You have configured multiple PAGE objects in TypoScript with typeNum = 0. Please correct that before using maps2.',
                    1524222485
                );
        }

        if (!$jsLibraryHasApiKey) {
            throw new \Exception(
                'Included JS lib for Google Maps does not contain ApiKey. Please check it within Constant Editor or in Setup TS directly',
                1524223595
            );
        }
    }

    /**
     * We need the TS with PAGE-objects to check, if Google Maps JavaScript ApiKey was integrated successfully
     *
     * @return array
     */
    protected function getTypoScriptWithPageObjects()
    {
        $fullTypoScript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        $tsWithPageObjects = [];
        foreach ($fullTypoScript as $key => $value) {
            if (
                is_string($value) && $value === 'PAGE'
                && isset($fullTypoScript[$key . '.']) && is_array($fullTypoScript[$key . '.'])
                && (
                    !isset($fullTypoScript[$key . '.']['typeNum'])
                    || (
                        isset($fullTypoScript[$key . '.']['typeNum'])
                        && $fullTypoScript[$key . '.']['typeNum'] === '0'
                    )
                )
            ) {
                $tsWithPageObjects[] = $fullTypoScript[$key . '.'];
            }
        }
        return $tsWithPageObjects;
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
        $view->assign('environment', [
            'settings' => $this->settings,
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'id' => $GLOBALS['TSFE']->id,
            'contentRecord' => $this->configurationManager->getContentObject()->data
        ]);
    }
}

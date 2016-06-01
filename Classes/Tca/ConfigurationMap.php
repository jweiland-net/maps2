<?php
namespace JWeiland\Maps2\Tca;

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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class ConfigurationMap
 *
 * @category Tca
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class ConfigurationMap
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \JWeiland\Maps2\Configuration\ExtConf
     */
    protected $extConf;

    /**
     * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
     */
    protected $hashService;

    /**
     * @var \TYPO3\CMS\Fluid\View\StandaloneView
     */
    protected $view;

    /**
     * @var \TYPO3\CMS\Core\Page\PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository
     */
    protected $poiCollectionRepository;

    /**
     * initializes this class
     */
    public function init()
    {
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->extConf = $this->objectManager->get('JWeiland\\Maps2\\Configuration\\ExtConf');
        $this->hashService = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Security\\Cryptography\\HashService');
        $this->view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $this->pageRenderer = $this->objectManager->get('TYPO3\\CMS\\Core\\Page\\PageRenderer');
        $this->poiCollectionRepository = $this->objectManager->get('JWeiland\\Maps2\\Domain\\Repository\\PoiCollectionRepository');
    }

    /**
     * Renders the Google map.

     * @param array $parentArray
     * @param object $pObj
     * @return string
     */
    public function render(array $parentArray, $pObj)
    {
        $this->init();
        $parentArray = $this->cleanUpParentArray($parentArray);

        // add Google Maps API
        $this->pageRenderer->addJsLibrary('maps2GoogleMapsApi', $this->extConf->getGoogleMapsLibrary(), 'text/javascript', false, true, '', true);

        return $this->getMapHtml($this->getConfiguration($parentArray));
    }

    /**
     * since TYPO3 7.5 $PA['row'] consists of arrays where TCA was configured as type "select"
     * Convert these types back to strings/int
     *
     * @param array $parentArray
     * @return array
     */
    protected function cleanUpParentArray(array $parentArray)
    {
        foreach ($parentArray['row'] as $field => $value) {
            $parentArray['row'][$field] = is_array($value) ? $value[0] : $value;
        }
        return $parentArray;
    }

    /**
     * get configuration array from PA array
     *
     * @param array $PA
     * @return array
     */
    public function getConfiguration(array $PA)
    {
        $config = array();

        // get poi collection model
        $uid = (int)$PA['row']['uid'];
        $poiCollection = $this->poiCollectionRepository->findByUid($uid);
        if ($poiCollection instanceof PoiCollection) {
            // set map center
            $config['latitude'] = ($poiCollection->getLatitude()) ? $poiCollection->getLatitude() : $this->extConf->getDefaultLatitude();
            $config['longitude'] = ($poiCollection->getLongitude()) ? $poiCollection->getLongitude() : $this->extConf->getDefaultLongitude();
            switch ($poiCollection->getCollectionType()) {
                case 'Route':
                case 'Area':
                    // set pois
                    /** @var $poi \JWeiland\Maps2\Domain\Model\Poi */
                    foreach ($poiCollection->getPois() as $poi) {
                        $latLng['latitude'] = $poi->getLatitude();
                        $latLng['longitude'] = $poi->getLongitude();
                        $config['pois'][] = $latLng;
                    }
                    if (!isset($config['pois'])) {
                        $config['pois'] = array();
                    }
                    break;
                case 'Radius':
                    $config['radius'] = ($poiCollection->getRadius()) ? $poiCollection->getRadius() : $this->extConf->getDefaultRadius();
                    $config['radius'] = $poiCollection->getRadius();
                    break;
                default:
                    break;
            }

            $config['address'] =  $PA['row']['address'];
            $config['collectionType'] = is_array($PA['row']['collection_type']) ? $PA['row']['collection_type'][0] : $PA['row']['collection_type'];
            $config['uid'] =  $uid;
            $config['TYPO3_SITE_URL'] = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
            if (GeneralUtility::compat_version('7.3')) {
                $config['ajaxUrlForModifyMarker'] = BackendUtility::getAjaxUrl('maps2Ajax');
            } else {
                $config['ajaxUrlForModifyMarker'] = $config['TYPO3_SITE_URL'] . 'typo3/ajax.php?ajaxID=maps2Ajax';
            }

            $hashArray['uid'] = $uid;
            $hashArray['collectionType'] = $PA['row']['collection_type'];
            $config['hash'] = $this->hashService->generateHmac(serialize($hashArray));
        }
        return $config;
    }

    /**
     * get parsed content from template
     *
     * @param array $config
     * @return string
     */
    protected function getMapHtml(array $config)
    {
        $extPath = ExtensionManagementUtility::extPath('maps2');
        $this->view->setTemplatePathAndFilename($extPath . 'Resources/Private/Templates/Tca/ConfigurationMap.html');
        $this->view->assign('config', json_encode($config));
        $this->view->assign('design', json_encode(ObjectAccess::getGettableProperties($this->extConf)));
        $content = $this->view->render() . chr(10);
        $content .= file_get_contents(
            $extPath . 'Resources/Private/Templates/Tca/ConfigurationMapFor' .
            ucfirst($config['collectionType']) . '.html'
        );

        return $content;
    }
}

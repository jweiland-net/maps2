<?php
namespace JWeiland\Maps2\Tca;

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
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * This class makes it possible to show Google Maps in backend forms.
 */
class GoogleMaps
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
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->extConf = $this->objectManager->get(ExtConf::class);
        $this->hashService = $this->objectManager->get(HashService::class);
        $this->view = $this->objectManager->get(StandaloneView::class);
        $this->pageRenderer = $this->objectManager->get(PageRenderer::class);
        $this->poiCollectionRepository = $this->objectManager->get(PoiCollectionRepository::class);
    }

    /**
     * Renders Google Maps.

     * @param array $parentArray
     * @param object $pObj
     *
     * @return string
     *
     * @throws \Exception
     */
    public function render(array $parentArray, $pObj)
    {
        $this->init();
        $parentArray = $this->cleanUpParentArray($parentArray);

        // add our GoogleMaps library as RequireJS module
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Maps2/GoogleMapsModule');
        // loadRequireJsModule has to be loaded before configuring additional paths, else all ext paths will not be initialized
        $this->pageRenderer->addRequireJsConfiguration([
            'paths' => [
                'async' => rtrim(
                    PathUtility::getRelativePath(
                        PATH_typo3,
                        GeneralUtility::getFileAbsFileName('EXT:maps2/Resources/Public/JavaScript/async')
                    ),
                    '/'
                )
            ]
        ]);
        // make Google Maps2 available as dependency for all RequireJS modules
        $this->pageRenderer->addJsInlineCode(
            'definegooglemaps',
            sprintf(
                '// convert Google Maps into an AMD module
                define("gmaps", ["async!%s"],
                function() {
                    // return the gmaps namespace for brevity
                    return window.google.maps;
                });',
                $this->extConf->getGoogleMapsLibrary()
            ),
            false
        );

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
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getConfiguration(array $PA)
    {
        $config = [];

        // get poi collection model
        $uid = (int)$PA['row']['uid'];
        $poiCollection = $this->poiCollectionRepository->findByUid($uid);
        if ($poiCollection instanceof PoiCollection) {
            // set map center
            $config['latitude'] = $poiCollection->getLatitude();
            $config['longitude'] = $poiCollection->getLongitude();
            $config['latitudeOrig'] = $poiCollection->getLatitudeOrig();
            $config['longitudeOrig'] = $poiCollection->getLongitudeOrig();
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
                        $config['pois'] = [];
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
     *
     * @return string
     */
    protected function getMapHtml(array $config)
    {
        $extPath = ExtensionManagementUtility::extPath('maps2');
        $this->view->setTemplatePathAndFilename($extPath . 'Resources/Private/Templates/Tca/GoogleMaps.html');
        $this->view->assign('config', json_encode($config));
        $this->view->assign('extConf', json_encode(ObjectAccess::getGettableProperties($this->extConf)));

        return $this->view->render();
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Form\Element;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Helper\MessageHelper;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Special backend FormEngine element to show Google Maps
 */
class GoogleMapsElement extends AbstractFormElement
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var HashService
     */
    protected $hashService;

    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var PoiCollectionRepository
     */
    protected $poiCollectionRepository;

    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    /**
     * Default field information enabled for this element.
     *
     * @var array
     */
    protected $defaultFieldInformation = [
        'tcaDescription' => [
            'renderType' => 'tcaDescription',
        ],
    ];

    public function init()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);
        $this->hashService = GeneralUtility::makeInstance(HashService::class);
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $this->poiCollectionRepository = $this->objectManager->get(PoiCollectionRepository::class);
        $this->messageHelper = GeneralUtility::makeInstance(MessageHelper::class);

        $this->checkApiKeys();
    }

    /**
     * Check configured API Keys
     */
    protected function checkApiKeys()
    {
        if (empty($this->extConf->getGoogleMapsJavaScriptApiKey())) {
            $this->messageHelper->addFlashMessage(
                'You have forgotten to set Google Maps JavaScript ApiKey in Extensionmanager.',
                'Missing JS API Key',
                FlashMessage::ERROR
            );
        }

        if (empty($this->extConf->getGoogleMapsGeocodeApiKey())) {
            $this->messageHelper->addFlashMessage(
                'You have forgotten to set Google Maps Geocode ApiKey in Extensionmanager.',
                'Missing GeoCode API Key',
                FlashMessage::ERROR
            );
        }
    }

    /**
     * This will render Google Maps within PoiCollection records with a marker you can drag and drop
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     * @throws \Exception
     */
    public function render()
    {
        $this->init();
        $resultArray = $this->initializeResultArray();
        $currentRecord = $this->cleanUpCurrentRecord($this->data['databaseRow']);
        $backendRelPath = 'EXT:maps2/';

        // loadRequireJsModule has to be loaded before configuring additional paths, else all ext paths will not be initialized
        $this->pageRenderer->addRequireJsConfiguration([
            'paths' => [
                'async' => rtrim(
                    PathUtility::getRelativePathTo(
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
        $resultArray['stylesheetFiles'][] = $backendRelPath . 'Resources/Public/Css/GoogleMapsModule.css';
        $resultArray['requireJsModules'][] = 'TYPO3/CMS/Maps2/GoogleMapsModule';

        $fieldInformationResult = $this->renderFieldInformation();
        $resultArray['html'] = sprintf(
            '%s%s%s%s',
            '<div class="formengine-field-item t3js-formengine-field-item">',
            $fieldInformationResult['html'],
            $this->getMapHtml($this->getConfiguration($currentRecord)),
            '</div>'
        );

        return $resultArray;
    }

    /**
     * Since TYPO3 7.5 $this->data['databaseRow'] consists of arrays where TCA was configured as type "select"
     * Convert these types back to strings/int
     *
     * @param array $currentRecord
     * @return array
     */
    protected function cleanUpCurrentRecord(array $currentRecord)
    {
        foreach ($currentRecord as $field => $value) {
            $currentRecord[$field] = is_array($value) ? $value[0] : $value;
        }
        return $currentRecord;
    }

    /**
     * Get configuration array from PA array
     *
     * @param array $currentRecord
     * @return array
     * @throws \Exception
     */
    protected function getConfiguration(array $currentRecord)
    {
        $config = [];

        // get poi collection model
        $uid = (int)$currentRecord['uid'];
        $poiCollection = $this->poiCollectionRepository->findByUid($uid);
        if ($poiCollection instanceof PoiCollection) {
            // set map center
            $config['latitude'] = $poiCollection->getLatitude();
            $config['longitude'] = $poiCollection->getLongitude();
            switch ($poiCollection->getCollectionType()) {
                case 'Route':
                case 'Area':
                    // set pois
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

            $config['address'] =  $currentRecord['address'];
            $config['collectionType'] = is_array($currentRecord['collection_type']) ? $currentRecord['collection_type'][0] : $currentRecord['collection_type'];
            $config['uid'] =  $uid;

            $hashArray['uid'] = $uid;
            $hashArray['collectionType'] = $currentRecord['collection_type'];
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

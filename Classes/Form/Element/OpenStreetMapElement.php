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
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Special backend FormEngine element to show Open Street Map
 */
class OpenStreetMapElement extends AbstractFormElement
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
     * Default field information enabled for this element.
     *
     * @var array
     */
    protected $defaultFieldInformation = [
        'tcaDescription' => [
            'renderType' => 'tcaDescription',
        ],
    ];

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
     * This will render Google Maps within PoiCollection records with a marker you can drag and drop
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     * @throws \Exception
     */
    public function render(): array
    {
        $this->init();
        $resultArray = $this->initializeResultArray();
        $currentRecord = $this->cleanUpCurrentRecord($this->data['databaseRow']);
        $backendRelPath = 'EXT:maps2/';

        $this->pageRenderer->addRequireJsConfiguration([
            'paths' => [
                'leaflet' => rtrim(
                    PathUtility::getRelativePathTo(
                        GeneralUtility::getFileAbsFileName('EXT:maps2/Resources/Public/JavaScript/Leaflet')
                    ),
                    '/'
                ),
                'leafletDragPath' => rtrim(
                    PathUtility::getRelativePathTo(
                        GeneralUtility::getFileAbsFileName('EXT:maps2/Resources/Public/JavaScript/Leaflet.Drag.Path')
                    ),
                    '/'
                ),
                'leafletEditable' => rtrim(
                    PathUtility::getRelativePathTo(
                        GeneralUtility::getFileAbsFileName('EXT:maps2/Resources/Public/JavaScript/Leaflet.Editable')
                    ),
                    '/'
                )
            ],
            'shim' => [
                'leaflet' => [
                    'deps' => ['jquery'],
                    'exports' => 'L'
                ],
                'leafletDragPath' => [
                    'deps' => ['leaflet'],
                ],
                'leafletEditable' => [
                    'deps' => ['leafletDragPath'],
                ],
            ]
        ]);

        $resultArray['stylesheetFiles'][] = $backendRelPath . 'Resources/Public/Css/Leaflet/Leaflet.css';
        $resultArray['requireJsModules'][] = [
            'TYPO3/CMS/Maps2/OpenStreetMapModule' => 'function(OpenStreetMap){OpenStreetMap();}'
        ];

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
    protected function cleanUpCurrentRecord(array $currentRecord): array
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
    protected function getConfiguration(array $currentRecord): array
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
     * Get parsed content from template
     *
     * @param array $config
     * @return string
     */
    protected function getMapHtml(array $config): string
    {
        $extPath = ExtensionManagementUtility::extPath('maps2');
        $this->view->setTemplatePathAndFilename($extPath . 'Resources/Private/Templates/Tca/OpenStreetMap.html');
        $this->view->assign('config', json_encode($config));
        $this->view->assign('extConf', json_encode(ObjectAccess::getGettableProperties($this->extConf)));

        return $this->view->render();
    }
}

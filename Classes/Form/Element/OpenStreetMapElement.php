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
use JWeiland\Maps2\Helper\MapHelper;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/*
 * Special backend FormEngine element to show Open Street Map.
 * This is a very reduced InputTextElement. The textfield itself will not be displayed,
 * but it contains the JSON for all the POIs.
 */
class OpenStreetMapElement extends AbstractFormElement
{
    private const ELEMENT_TEMPLATE = 'EXT:maps2/Resources/Private/Templates/Tca/OpenStreetMap.html';

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

    public function __construct(
        protected readonly ExtConf $extConf,
        protected readonly PageRenderer $pageRenderer,
        protected readonly MapHelper $mapHelper,
        protected readonly ViewFactoryInterface $viewFactory,
        protected NodeFactory $nodeFactory,
    ) {}

    /**
     * This will render Google Maps within PoiCollection records with a marker you can drag and drop
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     * @throws \Exception
     */
    public function render(): array
    {
        $parameterArray = $this->data['parameterArray'];
        $resultArray = $this->initializeResultArray();

        $itemValue = $parameterArray['itemFormElValue'];
        $config = $parameterArray['fieldConf']['config'];
        $evalList = GeneralUtility::trimExplode(',', $config['eval'] ?? '', true);

        $this->pageRenderer->loadJavaScriptModule('@jweiland/maps2/leaflet.min.js');

        $resultArray['stylesheetFiles'][] = PathUtility::getPublicResourceWebPath(
            'EXT:maps2/Resources/Public/Css/Leaflet/Leaflet.css',
        );

        $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create(
            '@jweiland/maps2/OpenStreetMapModule.min.js',
        );

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'] ?? '';

        $attributes = [
            'value' => '',
            'id' => StringUtility::getUniqueId('formengine-input-'),
            'class' => implode(' ', [
                'form-control',
                't3js-clearable',
                'hasDefaultValue',
            ]),
            'data-formengine-validation-rules' => $this->getValidationDataAsJsonString($config),
            'data-formengine-input-params' => (string)json_encode([
                'field' => $parameterArray['itemFormElName'] ?? '',
                'evalList' => implode(',', $evalList),
                'is_in' => '',
            ]),
            'data-formengine-input-name' => (string)($parameterArray['itemFormElName'] ?? ''),
        ];

        // SF: We can not set this field to type="hidden" as FormEngine.getFieldElement
        // will not find it. That's why I work with display: none;
        $attributes['style'] = 'display: none;';

        $html = [];
        $html[] = '<div class="form-control-wrap">';
        $html[] =     '<div class="form-wizards-wrap">';
        $html[] =         '<div class="form-wizards-element">';
        $html[] =             $this->getMapHtml($this->cleanUpPoiCollectionRecord($this->data['databaseRow']));
        $html[] =             '<input type="text" ' . GeneralUtility::implodeAttributes($attributes, true) . ' />';
        $html[] =             '<input type="hidden" name="' . ($parameterArray['itemFormElName'] ?? '') . '" value="' . htmlspecialchars((string)$itemValue) . '" />';
        $html[] =         '</div>';
        $html[] =     '</div>';
        $html[] = '</div>';

        $resultArray['html'] = sprintf(
            '<div class="formengine-field-item t3js-formengine-field-item">%s%s</div>',
            $fieldInformationHtml,
            implode(LF, $html),
        );

        return $resultArray;
    }

    /**
     * Since TYPO3 7.5 $this->data['databaseRow'] consists of arrays where TCA was configured as type "select"
     * Convert these types back to strings/int
     */
    protected function cleanUpPoiCollectionRecord(array $poiCollectionRecord): array
    {
        foreach ($poiCollectionRecord as $field => $value) {
            if ($field === 'configuration_map') {
                $poiCollectionRecord[$field] = $this->mapHelper->convertPoisAsJsonToArray($value);
            } else {
                $poiCollectionRecord[$field] = is_array($value) && array_key_exists(0, $value) ? $value[0] : $value;
            }
        }

        return $poiCollectionRecord;
    }

    protected function getMapHtml(array $poiCollectionRecord): string
    {
        $view = $this->viewFactory->create(new ViewFactoryData(
            templatePathAndFilename: self::ELEMENT_TEMPLATE,
        ));

        $view->assign('poiCollection', json_encode($poiCollectionRecord, JSON_THROW_ON_ERROR));
        $view->assign('extConf', json_encode(
            ObjectAccess::getGettableProperties($this->extConf),
            JSON_THROW_ON_ERROR,
        ));

        return $view->render();
    }
}

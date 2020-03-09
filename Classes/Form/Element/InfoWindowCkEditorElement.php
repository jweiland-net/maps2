<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Form\Element;

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

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Show info window content as a read-only field at second tab in backend form
 */
class InfoWindowCkEditorElement extends AbstractFormElement
{
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
     * Default field wizards enabled for this element.
     *
     * @var array
     */
    protected $defaultFieldWizard = [
        'localizationStateSelector' => [
            'renderType' => 'localizationStateSelector',
        ],
        'otherLanguageContent' => [
            'renderType' => 'otherLanguageContent',
            'after' => [
                'localizationStateSelector'
            ],
        ],
        'defaultLanguageDifferences' => [
            'renderType' => 'defaultLanguageDifferences',
            'after' => [
                'otherLanguageContent',
            ],
        ],
    ];

    /**
     * This property contains configuration related to the RTE
     * But only the .editor configuration part
     *
     * @var array
     */
    protected $rteConfiguration = [];

    /**
     * Renders the ckeditor element
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function render(): array
    {
        $resultArray = $this->initializeResultArray();
        $parameterArray = $this->data['parameterArray'];
        $config = $parameterArray['fieldConf']['config'];

        $fieldId = $this->sanitizeFieldId($parameterArray['itemFormElName']);
        $itemFormElementName = $this->data['parameterArray']['itemFormElName'];

        $value = $this->data['parameterArray']['itemFormElValue'] ?? '';

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldInformationResult, false);

        $fieldControlResult = $this->renderFieldControl();
        $fieldControlHtml = $fieldControlResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldControlResult, false);

        $fieldWizardResult = $this->renderFieldWizard();
        $fieldWizardHtml = $fieldWizardResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldWizardResult, false);

        $attributes = [
            'style' => 'display:none',
            'data-formengine-validation-rules' => $this->getValidationDataAsJsonString($config),
            'id' => $fieldId,
            'name' => htmlspecialchars($itemFormElementName),
        ];

        $address = GeneralUtility::trimExplode(',', $this->data['databaseRow']['address']);
        $addressHeader = $this->getLanguageService()->sL('LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.info_window_address');

        $html = [];
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item">';
        $html[] =   $fieldInformationHtml;
        $html[] =   '<div class="form-control-wrap">';
        $html[] =       '<div class="callout callout-info">';
        $html[] =           '<div class="media">';
        $html[] =               '<div class="media-body">';
        $html[] =                   '<h4 class="callout-title">' . htmlspecialchars($addressHeader) . '</h4>';
        $html[] =                   '<div class="callout-body"><strong>' . $this->data['databaseRow']['title'] . '</strong><br>' . implode('<br />', $address) . '<br /><br /></div>';
        $html[] =                   '<div class="callout-body">' . $this->data['databaseRow']['info_window_content'] . '</div>';
        $html[] =               '</div>';
        $html[] =           '</div>';
        $html[] =       '</div>';
        $html[] =       '<div class="form-wizards-wrap">';
        $html[] =           '<div class="form-wizards-element">';
        $html[] =               '<textarea ' . GeneralUtility::implodeAttributes($attributes, true) . '>';
        $html[] =                   htmlspecialchars($value);
        $html[] =               '</textarea>';
        $html[] =           '</div>';
        if (!empty($fieldControlHtml)) {
            $html[] =           '<div class="form-wizards-items-aside">';
            $html[] =               '<div class="btn-group">';
            $html[] =                   $fieldControlHtml;
            $html[] =               '</div>';
            $html[] =           '</div>';
        }
        if (!empty($fieldWizardHtml)) {
            $html[] = '<div class="form-wizards-items-bottom">';
            $html[] = $fieldWizardHtml;
            $html[] = '</div>';
        }
        $html[] =       '</div>';
        $html[] =   '</div>';
        $html[] = '</div>';

        $resultArray['html'] = implode(LF, $html);

        $this->rteConfiguration = $config['richtextConfiguration']['editor'];
        $resultArray['requireJsModules'][] = [
            'ckeditor' => $this->getCkEditorRequireJsModuleCode($fieldId)
        ];

        return $resultArray;
    }

    /**
     * Determine the contents language iso code
     *
     * @return string
     */
    protected function getLanguageIsoCodeOfContent(): string
    {
        $currentLanguageUid = $this->data['databaseRow']['sys_language_uid'];
        if (is_array($currentLanguageUid)) {
            $currentLanguageUid = $currentLanguageUid[0];
        }
        $contentLanguageUid = (int)max($currentLanguageUid, 0);
        if ($contentLanguageUid) {
            $contentLanguage = $this->data['systemLanguageRows'][$currentLanguageUid]['iso'];
        } else {
            $contentLanguage = $this->rteConfiguration['config']['defaultContentLanguage'] ?? 'en_US';
            $languageCodeParts = explode('_', $contentLanguage);
            $contentLanguage = strtolower($languageCodeParts[0]) . ($languageCodeParts[1] ? '_' . strtoupper($languageCodeParts[1]) : '');
            // Find the configured language in the list of localization locales
            $locales = GeneralUtility::makeInstance(Locales::class);
            // If not found, default to 'en'
            if (!in_array($contentLanguage, $locales->getLocales(), true)) {
                $contentLanguage = 'en';
            }
        }
        return $contentLanguage;
    }

    /**
     * Gets the JavaScript code for CKEditor module
     * Compiles the configuration, and then adds plugins
     *
     * @param string $fieldId
     * @return string
     */
    protected function getCkEditorRequireJsModuleCode(string $fieldId): string
    {
        $configuration = $this->prepareConfigurationForEditor();

        $externalPlugins = '';
        foreach ($this->getExtraPlugins() as $pluginName => $config) {
            if (!empty($config['config']) && !empty($configuration[$pluginName])) {
                $config['config'] = array_replace_recursive($config['config'], $configuration[$pluginName]);
            }
            $configuration[$pluginName] = $config['config'];
            $configuration['extraPlugins'] .= ',' . $pluginName;

            $externalPlugins .= 'CKEDITOR.plugins.addExternal(';
            $externalPlugins .= GeneralUtility::quoteJSvalue($pluginName) . ',';
            $externalPlugins .= GeneralUtility::quoteJSvalue($config['resource']) . ',';
            $externalPlugins .= '\'\');';
        }

        return 'function(CKEDITOR) {
                ' . $externalPlugins . '
                $(function(){
                    CKEDITOR.replace("' . $fieldId . '", ' . json_encode($configuration) . ');
                    require([\'jquery\', \'TYPO3/CMS/Backend/FormEngine\'], function($, FormEngine) {
                        CKEDITOR.instances["' . $fieldId . '"].on(\'change\', function() {
                            CKEDITOR.instances["' . $fieldId . '"].updateElement();
                            FormEngine.Validation.validate();
                            FormEngine.Validation.markFieldAsChanged($(\'#' . $fieldId . '\'));
                        });
                        $(document).on(\'inline:sorting-changed\', function() {
                            CKEDITOR.instances["' . $fieldId . '"].destroy();
                            CKEDITOR.replace("' . $fieldId . '", ' . json_encode($configuration) . ');
                        });
                        $(document).on(\'flexform:sorting-changed\', function() {
                            CKEDITOR.instances["' . $fieldId . '"].destroy();
                            CKEDITOR.replace("' . $fieldId . '", ' . json_encode($configuration) . ');
                        });
                    });
                });
        }';
    }

    /**
     * Get configuration of external/additional plugins
     *
     * @return array
     */
    protected function getExtraPlugins(): array
    {
        $urlParameters = [
            'P' => [
                'table'      => $this->data['tableName'],
                'uid'        => $this->data['databaseRow']['uid'],
                'fieldName'  => $this->data['fieldName'],
                'recordType' => $this->data['recordTypeValue'],
                'pid'        => $this->data['effectivePid'],
                'richtextConfigurationName' => $this->data['parameterArray']['fieldConf']['config']['richtextConfigurationName']
            ]
        ];

        $pluginConfiguration = [];
        if (isset($this->rteConfiguration['externalPlugins']) && is_array($this->rteConfiguration['externalPlugins'])) {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            foreach ($this->rteConfiguration['externalPlugins'] as $pluginName => $configuration) {
                $pluginConfiguration[$pluginName] = [
                    'resource' => $this->resolveUrlPath($configuration['resource'])
                ];
                unset($configuration['resource']);

                if ($configuration['route']) {
                    $configuration['routeUrl'] = (string)$uriBuilder->buildUriFromRoute($configuration['route'], $urlParameters);
                }

                $pluginConfiguration[$pluginName]['config'] = $configuration;
            }
        }
        return $pluginConfiguration;
    }

    /**
     * Add configuration to replace LLL: references with the translated value
     * @param array $configuration
     *
     * @return array
     */
    protected function replaceLanguageFileReferences(array $configuration): array
    {
        foreach ($configuration as $key => $value) {
            if (is_array($value)) {
                $configuration[$key] = $this->replaceLanguageFileReferences($value);
            } elseif (is_string($value) && stripos($value, 'LLL:') === 0) {
                $configuration[$key] = $this->getLanguageService()->sL($value);
            }
        }
        return $configuration;
    }

    /**
     * Add configuration to replace absolute EXT: paths with relative ones
     * @param array $configuration
     *
     * @return array
     */
    protected function replaceAbsolutePathsToRelativeResourcesPath(array $configuration): array
    {
        foreach ($configuration as $key => $value) {
            if (is_array($value)) {
                $configuration[$key] = $this->replaceAbsolutePathsToRelativeResourcesPath($value);
            } elseif (is_string($value) && stripos($value, 'EXT:') === 0) {
                $configuration[$key] = $this->resolveUrlPath($value);
            }
        }
        return $configuration;
    }

    /**
     * Resolves an EXT: syntax file to an absolute web URL
     *
     * @param string $value
     * @return string
     */
    protected function resolveUrlPath(string $value): string
    {
        $value = GeneralUtility::getFileAbsFileName($value);
        return PathUtility::getAbsoluteWebPath($value);
    }

    /**
     * Compiles the configuration set from the outside
     * to have it easily injected into the CKEditor.
     *
     * @return array the configuration
     */
    protected function prepareConfigurationForEditor(): array
    {
        // Ensure custom config is empty so nothing additional is loaded
        // Of course this can be overridden by the editor configuration below
        $configuration = [
            'customConfig' => '',
        ];

        if (is_array($this->rteConfiguration['config'])) {
            $configuration = array_replace_recursive($configuration, $this->rteConfiguration['config']);
        }
        // Set the UI language of the editor if not hard-coded by the existing configuration
        if (empty($configuration['language'])) {
            $configuration['language'] = $this->getBackendUser()->uc['lang'] ?: ($this->getBackendUser()->user['lang'] ?: 'en');
        }
        $configuration['contentsLanguage'] = $this->getLanguageIsoCodeOfContent();

        // Replace all label references
        $configuration = $this->replaceLanguageFileReferences($configuration);
        // Replace all paths
        $configuration = $this->replaceAbsolutePathsToRelativeResourcesPath($configuration);

        // there are some places where we define an array, but it needs to be a list in order to work
        if (is_array($configuration['extraPlugins'])) {
            $configuration['extraPlugins'] = implode(',', $configuration['extraPlugins']);
        }
        if (is_array($configuration['removePlugins'])) {
            $configuration['removePlugins'] = implode(',', $configuration['removePlugins']);
        }
        if (is_array($configuration['removeButtons'])) {
            $configuration['removeButtons'] = implode(',', $configuration['removeButtons']);
        }

        return $configuration;
    }

    /**
     * @param string $itemFormElementName
     * @return string
     */
    protected function sanitizeFieldId(string $itemFormElementName): string
    {
        $fieldId = preg_replace('/[^a-zA-Z0-9_:.-]/', '_', $itemFormElementName);
        return htmlspecialchars(preg_replace('/^[^a-zA-Z]/', 'x', $fieldId));
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}

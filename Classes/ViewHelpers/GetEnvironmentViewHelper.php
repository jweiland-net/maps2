<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers;

use JWeiland\Maps2\Configuration\ExtConf;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * If you make use of our Partial/PoiCollection or Partial/EditPoiCollection we need some additional information
 * about current record and Extension Settings of maps2. Use this VH to add these additional variables to template.
 */
class GetEnvironmentViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Convert all array and object types into a json string. Useful for data-Attributes
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $templateVariableContainer = $renderingContext->getVariableProvider();

        $templateVariableContainer->add(
            'environment',
            [
                'settings' => self::getMaps2TypoScriptSettings(),
                'extConf' => ObjectAccess::getGettableProperties(GeneralUtility::makeInstance(ExtConf::class)),
                'id' => $GLOBALS['TSFE']->id,
                'contentRecord' => self::getConfigurationManager()->getContentObject()->data
            ]
        );

        $content = $renderChildrenClosure();

        $templateVariableContainer->remove('environment');

        return $content;
    }

    protected static function getMaps2TypoScriptSettings(): array
    {
        $fullTypoScript = self::getConfigurationManager()->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        if (ArrayUtility::isValidPath($fullTypoScript, 'plugin./tx_maps2./settings.')) {
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $settings = ArrayUtility::getValueByPath($fullTypoScript, 'plugin./tx_maps2./settings.');

            return $typoScriptService->convertTypoScriptArrayToPlainArray($settings);
        }

        return [];
    }

    protected static function getConfigurationManager(): ConfigurationManagerInterface
    {
        return GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
    }
}

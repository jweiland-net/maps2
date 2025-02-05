<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Backend\Preview;

use JWeiland\Maps2\Service\PoiCollectionService;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Add plugin preview for EXT:maps2
 */
class Maps2PluginPreview extends StandardContentPreviewRenderer
{
    private const PREVIEW_TEMPLATE = 'EXT:maps2/Resources/Private/Templates/PluginPreview/Maps2.html';

    private const ALLOWED_PLUGINS = [
        'maps2_maps2',
        'maps2_citymap',
        'maps2_searchwithinradius',
    ];

    public function __construct(
        protected FlexFormService $flexFormService,
        protected PoiCollectionService $poiCollectionService,
        protected ViewFactoryInterface $viewFactory,
    ) {}

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $ttContentRecord = $item->getRecord();
        if (!$this->isValidPlugin($ttContentRecord)) {
            return '';
        }

        $view = $this->viewFactory->create(new ViewFactoryData(
            templatePathAndFilename: self::PREVIEW_TEMPLATE,
        ));
        $view->assignMultiple($ttContentRecord);

        $this->addPluginName($view, $ttContentRecord);

        // Add data from column pi_flexform
        $piFlexformData = $this->getPiFlexformData($ttContentRecord);
        if ($piFlexformData !== []) {
            $view->assign('pi_flexform_transformed', $piFlexformData);
        }

        if ($ttContentRecord['CType'] === 'maps2_maps2') {
            $this->addPoiCollection($view, $piFlexformData);
        }

        return $view->render();
    }

    protected function isValidPlugin(array $ttContentRecord): bool
    {
        if (!isset($ttContentRecord['CType'])) {
            return false;
        }

        if (!in_array($ttContentRecord['CType'], self::ALLOWED_PLUGINS, true)) {
            return false;
        }

        return true;
    }

    protected function addPluginName(ViewInterface $view, array $ttContentRecord): void
    {
        $langKey = sprintf(
            'plugin.%s.title',
            str_replace('maps2_', '', $ttContentRecord['CType']),
        );

        $view->assign(
            'pluginName',
            LocalizationUtility::translate('LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:' . $langKey),
        );
    }

    protected function getPiFlexformData(array $ttContentRecord): array
    {
        $data = [];
        if (!empty($ttContentRecord['pi_flexform'] ?? '')) {
            $data = $this->flexFormService->convertFlexFormContentToArray($ttContentRecord['pi_flexform']);
        }

        return $data;
    }

    protected function addPoiCollection(ViewInterface $view, array $piFlexformData): void
    {
        if (
            isset($piFlexformData['settings']['poiCollection'])
            && $piFlexformData['settings']['poiCollection'] !== '0'
            && MathUtility::canBeInterpretedAsInteger($piFlexformData['settings']['poiCollection'])
        ) {
            $poiCollectionRecord = $this->poiCollectionService->findByUid(
                (int)$piFlexformData['settings']['poiCollection'],
                $GLOBALS['TYPO3_REQUEST'] ?? null,
            );
            if ($poiCollectionRecord !== null) {
                $view->assign('poiCollectionRecord', $poiCollectionRecord);
            }
        }
    }
}

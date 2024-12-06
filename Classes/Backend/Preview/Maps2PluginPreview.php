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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Add plugin preview for EXT:maps2
 */
class Maps2PluginPreview extends StandardContentPreviewRenderer
{
    protected const string PREVIEW_TEMPLATE = 'EXT:maps2/Resources/Private/Templates/PluginPreview/Maps2.html';

    public function __construct(
        protected FlexFormService $flexFormService,
        protected PoiCollectionService $poiCollectionService
    ) {}

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $ttContentRecord = $item->getRecord();
        if (!$this->isValidPlugin($ttContentRecord)) {
            return '';
        }

        $view = $this->getStandaloneView();
        $view->assignMultiple($ttContentRecord);

        $this->addPluginName($view, $ttContentRecord);

        // Add data from column pi_flexform
        $piFlexformData = $this->getPiFlexformData($ttContentRecord);
        if ($piFlexformData !== []) {
            $view->assign('pi_flexform_transformed', $piFlexformData);
        }

        if ($ttContentRecord['list_type'] === 'maps2_maps2') {
            $this->addPoiCollection($view, $piFlexformData);
        }

        return $view->render();
    }

    protected function isValidPlugin(array $ttContentRecord): bool
    {
        if (!isset($ttContentRecord['list_type'])) {
            return false;
        }

        if (!in_array($ttContentRecord['list_type'], ['maps2_maps2', 'maps2_citymap', 'maps2_searchwithinradius'], true)) {
            return false;
        }

        return true;
    }

    protected function addPluginName(StandaloneView $view, array $ttContentRecord): void
    {
        $pluginName = match ($ttContentRecord['list_type']) {
            'maps2_citymap' => 'cityMap',
            'maps2_searchwithinradius' => 'radius',
            default => 'maps',
        };

        $langKey = sprintf(
            'plugin.%s.title',
            $pluginName,
        );

        $view->assign(
            'pluginName',
            LocalizationUtility::translate('LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:' . $langKey),
        );
    }

    protected function getStandaloneView(): StandaloneView
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(self::PREVIEW_TEMPLATE);

        return $view;
    }

    protected function getPiFlexformData(array $ttContentRecord): array
    {
        $data = [];
        if (!empty($ttContentRecord['pi_flexform'] ?? '')) {
            $data = $this->flexFormService->convertFlexFormContentToArray($ttContentRecord['pi_flexform']);
        }

        return $data;
    }

    protected function addPoiCollection(StandaloneView $view, array $piFlexformData): void
    {
        if (
            isset($piFlexformData['settings']['poiCollection'])
            && $piFlexformData['settings']['poiCollection'] !== '0'
            && MathUtility::canBeInterpretedAsInteger($piFlexformData['settings']['poiCollection'])
        ) {
            $poiCollectionRecord = $this->poiCollectionService->findByUid(
                (int)$piFlexformData['settings']['poiCollection']
            );
            if ($poiCollectionRecord !== null) {
                $view->assign('poiCollectionRecord', $poiCollectionRecord);
            }
        }
    }
}

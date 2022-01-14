<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Form\FieldInformation;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render content of column info_window_content above the RTE field
 */
class InfoWindowContent extends AbstractNode
{
    /**
     * @return mixed[]
     */
    public function render(): array
    {
        $resultArray = $this->initializeResultArray();

        $address = GeneralUtility::trimExplode(',', $this->data['databaseRow']['address']);

        $html = [];
        $html[] = '<div class="callout callout-info">';
        $html[] =   '<div class="media">';
        $html[] =     '<div class="media-body">';
        $html[] =       '<div class="callout-body"><strong>' . $this->data['databaseRow']['title'] . '</strong><br>' . implode('<br />', $address) . '<br /><br /></div>';
        $html[] =       '<div class="callout-body">' . $this->data['databaseRow']['info_window_content'] . '</div>';
        $html[] =     '</div>';
        $html[] =   '</div>';
        $html[] = '</div>';

        $resultArray['html'] = implode('', $html);

        return $resultArray;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}

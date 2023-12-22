<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client;

use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Open Street Map Client which will send Requests to Open Street Map Servers
 */
class OpenStreetMapClient extends AbstractClient
{
    protected string $title = 'Open Street Map';

    protected function checkResponseForErrors(?array $processedResponse): void
    {
        if ($processedResponse === null) {
            $this->messageHelper->addFlashMessage(
                'The response of Open Street Map was not a valid JSON response.',
                'Invalid JSON response',
                ContextualFeedbackSeverity::ERROR
            );
        } elseif ($processedResponse === []) {
            $this->messageHelper->addFlashMessage(
                LocalizationUtility::translate(
                    'error.noPositionsFound.body',
                    'maps2',
                    [
                        0 => $this->title,
                    ]
                ),
                LocalizationUtility::translate(
                    'error.noPositionsFound.title',
                    'maps2'
                ),
                ContextualFeedbackSeverity::ERROR
            );
        }
    }
}

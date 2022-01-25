<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client;

use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Google Maps Client which will send Requests to Google Maps Servers
 */
class GoogleMapsClient extends AbstractClient
{
    protected string $title = 'Google Maps';

    protected function checkResponseForErrors(?array $response): void
    {
        if ($response === null) {
            $this->messageHelper->addFlashMessage(
                'The response of Google Maps was not a valid JSON response.',
                'Invalid JSON response',
                AbstractMessage::ERROR
            );
        } elseif ($response['status'] !== 'OK') {
            if ($response['status'] === 'ZERO_RESULTS') {
                $this->messageHelper->addFlashMessage(
                    LocalizationUtility::translate(
                        'error.noPositionsFound.body',
                        'maps2',
                        [
                            0 => $this->title
                        ]
                    ),
                    LocalizationUtility::translate(
                        'error.noPositionsFound.title',
                        'maps2'
                    ),
                    AbstractMessage::ERROR
                );
            } else {
                $this->messageHelper->addFlashMessage(
                    $response['error_message'],
                    'Error',
                    AbstractMessage::ERROR
                );
            }
        }
    }
}

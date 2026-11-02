<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client;

use JWeiland\Maps2\Configuration\MapProviderEnum;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Google Maps Client that will send Requests to Google Maps Servers
 */
#[AutoconfigureTag(
    name: 'maps2.client.geocoding',
)]
readonly class GoogleMapsClient extends AbstractClient
{
    protected const TITLE = 'Google Maps';

    public function canProcess(MapProviderEnum $mapProvider): bool
    {
        return $mapProvider === MapProviderEnum::GOOGLE_MAPS;
    }

    protected function getResponseError(?array $processedResponse): ?ClientError
    {
        if ($processedResponse === null) {
            return new ClientError(
                ClientErrorEnum::INVALID_RESPONSE,
                'The response of Google Maps was not a valid JSON response.',
                'Invalid JSON response',
            );
        }

        $status = (string)($processedResponse['status'] ?? '');
        return match ($status) {
            'OK' => null,
            'ZERO_RESULTS' => new ClientError(
                ClientErrorEnum::NO_POSITIONS_FOUND,
                LocalizationUtility::translate('error.noPositionsFound.body', 'maps2', [0 => self::TITLE]) ?? '',
                LocalizationUtility::translate('error.noPositionsFound.title', 'maps2') ?? '',
            ),
            default => new ClientError(
                $this->getErrorByStatus($status),
                (string)($processedResponse['error_message'] ?? $status),
                'Error',
            ),
        };
    }

    /**
     * @link https://developers.google.com/maps/documentation/geocoding/requests-geocoding#StatusCodes
     */
    protected function getErrorByStatus(string $status): ClientErrorEnum
    {
        return match ($status) {
            'REQUEST_DENIED' => ClientErrorEnum::REQUEST_DENIED,
            'OVER_QUERY_LIMIT', 'OVER_DAILY_LIMIT' => ClientErrorEnum::QUOTA_EXCEEDED,
            'INVALID_REQUEST' => ClientErrorEnum::INVALID_REQUEST,
            default => ClientErrorEnum::SERVICE_ERROR,
        };
    }
}

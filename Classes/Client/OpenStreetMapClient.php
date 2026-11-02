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
 * OpenStreetMap Client that will send Requests to OpenStreetMap Servers
 */
#[AutoconfigureTag(
    name: 'maps2.client.geocoding',
)]
readonly class OpenStreetMapClient extends AbstractClient
{
    protected const TITLE = 'Open Street Map';

    public function canProcess(MapProviderEnum $mapProvider): bool
    {
        return $mapProvider === MapProviderEnum::OPEN_STREET_MAP;
    }

    protected function getResponseError(?array $processedResponse): ?ClientError
    {
        if ($processedResponse === null) {
            return new ClientError(
                ClientErrorEnum::INVALID_RESPONSE,
                'The response of Open Street Map was not a valid JSON response.',
                'Invalid JSON response',
            );
        }

        if ($processedResponse === []) {
            return new ClientError(
                ClientErrorEnum::NO_POSITIONS_FOUND,
                LocalizationUtility::translate('error.noPositionsFound.body', 'maps2', [0 => self::TITLE]) ?? '',
                LocalizationUtility::translate('error.noPositionsFound.title', 'maps2') ?? '',
            );
        }

        return null;
    }
}

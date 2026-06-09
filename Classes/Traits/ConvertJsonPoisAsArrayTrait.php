<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Traits;

use TYPO3\CMS\Core\Utility\GeneralUtility;

trait ConvertJsonPoisAsArrayTrait
{
    /**
     * POIs are stored as JSON in tx_maps_domain_model_poicollection.
     * Use this method to convert the JSON back into an array.
     *
     * @param string $poisAsJson That's normally the content of column "configuration_map"
     * @return array<string, string>[]|bool[]
     */
    public function convertJsonPoisToArray(string $poisAsJson): array
    {
        $pois = [];

        try {
            foreach (json_decode($poisAsJson, true, 512, JSON_THROW_ON_ERROR) ?? [] as $poi) {
                $pois[] = array_combine(
                    [
                        'latitude',
                        'longitude',
                    ],
                    GeneralUtility::trimExplode(',', $poi),
                );
            }
        } catch (\JsonException) {
            // Return empty POIs
        }

        return $pois;
    }
}

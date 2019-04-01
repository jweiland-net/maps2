<?php
declare(strict_types=1);
namespace JWeiland\Maps2\Hook;

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

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * With maps2 5.0.0 we have moved some FlexForm Settings to another sheet
 * To prevent duplicates in DB, this hook removes old settings from DB before save.
 */
class MoveOldFlexFormSettingsHook
{
    /**
     * Remove old fields from FlexForm Settings before merge
     *
     * @param DataHandler $dataHandler
     * @param array $valueFromDatabase
     * @param array $valueToStore
     */
    public function checkFlexFormValue_beforeMerge(DataHandler $dataHandler, array &$valueFromDatabase, array &$valueToStore)
    {
        if ($this->isValid($dataHandler)) {
            $this->moveSheetDefaultToDef($valueFromDatabase);
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.activateScrollWheel', 'sMapOptions', 'sGoogleMapsOptions');
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.mapTypeControl', 'sMapOptions', 'sGoogleMapsOptions');
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.mapTypeId', 'sMapOptions', 'sGoogleMapsOptions');
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.scaleControl', 'sMapOptions', 'sGoogleMapsOptions');
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.streetViewControl', 'sMapOptions', 'sGoogleMapsOptions');
            $this->moveFieldFromOldToNewSheet($valueFromDatabase, 'settings.styles', 'sMapOptions', 'sGoogleMapsOptions');
            unset($valueFromDatabase['data']['sGoogleMapsOptions']['lDEF']['settings.fullScreenControl']);
        }
    }

    /**
     * Returns true, if current save request is valid for this hook
     *
     * @param DataHandler $dataHandler
     * @return bool
     */
    protected function isValid(DataHandler $dataHandler)
    {
        $isValid = false;

        // Process only, if a record of type tt_content was saved
        if (!array_key_exists('tt_content', $dataHandler->datamap)) {
            return $isValid;
        }

        foreach ($dataHandler->datamap['tt_content'] as $uid => $record) {
            if ($record['CType'] === 'list' && $record['list_type'] === 'maps2_maps2') {
                $isValid = true;
                break;
            }
        }

        return $isValid;
    }

    /**
     * It's not a must have, but sDEF seems to be more default than sDEFAULT as first sheet name in TYPO3
     *
     * @param array $valueFromDatabase
     */
    protected function moveSheetDefaultToDef(array &$valueFromDatabase)
    {
        if (array_key_exists('sDEFAULT', $valueFromDatabase['data'])) {
            foreach ($valueFromDatabase['data']['sDEFAULT']['lDEF'] as $field => $value) {
                $this->moveFieldFromOldToNewSheet($valueFromDatabase, $field, 'sDEFAULT', 'sDEF');
            }

            // remove old sheet completely
            unset($valueFromDatabase['data']['sDEFAULT']);
        }
    }

    /**
     * Move field from one sheet to another and remove field from old location
     *
     * @param array $valueFromDatabase
     * @param string $field
     * @param string $oldSheet
     * @param string $newSheet
     */
    protected function moveFieldFromOldToNewSheet(array &$valueFromDatabase, string $field, string $oldSheet, string $newSheet)
    {
        if (array_key_exists($field, $valueFromDatabase['data'][$oldSheet]['lDEF'])) {
            // Create base sheet, if not exist
            if (!array_key_exists($newSheet, $valueFromDatabase['data'])) {
                $valueFromDatabase['data'][$newSheet] = [
                    'lDEF' => []
                ];
            }

            // Move field to new location, if not already done
            if (!array_key_exists($field, $valueFromDatabase['data'][$newSheet]['lDEF'])) {
                $valueFromDatabase['data'][$newSheet]['lDEF'][$field] = $valueFromDatabase['data'][$oldSheet]['lDEF'][$field];
            }

            // Remove old reference
            unset($valueFromDatabase['data'][$oldSheet]['lDEF'][$field]);
        }
    }
}

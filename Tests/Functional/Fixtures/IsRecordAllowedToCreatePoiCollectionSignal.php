<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Tests\Functional\Fixtures;

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

/**
 * SignalSlot to NOT create a new PoiCollection
 */
class IsRecordAllowedToCreatePoiCollectionSignal
{
    /**
     * This SignalSlot disallows the creation of new PoiCollection
     *
     * @param array $foreignLocationRecord
     * @param string $foreignTableName
     * @param string $foreignColumnName
     * @param array $options
     * @param bool $isValid
     */
    public function invalidPoiCollection(array $foreignLocationRecord, string $foreignTableName, string $foreignColumnName, array $options, bool &$isValid)
    {
        $isValid = false;
    }
}

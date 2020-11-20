<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Fixtures;

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
    public function invalidPoiCollection(
        array $foreignLocationRecord,
        string $foreignTableName,
        string $foreignColumnName,
        array $options,
        bool &$isValid
    ): void {
        $isValid = false;
    }
}

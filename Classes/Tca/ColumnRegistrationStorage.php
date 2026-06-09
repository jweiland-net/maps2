<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tca;

final class ColumnRegistrationStorage extends \ArrayObject
{
    public function isTableRegistered(string $table): bool
    {
        /** @var ColumnRegistration $columnRegistration */
        foreach ($this as $columnRegistration) {
            if ($columnRegistration->getTableName() === $table) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ColumnRegistration[]
     */
    public function getColumnRegistrations(string $table): array
    {
        $columnRegistrations = [];

        /** @var ColumnRegistration $columnRegistration */
        foreach ($this as $columnRegistration) {
            if ($columnRegistration->getTableName() === $table) {
                $columnRegistrations[$columnRegistration->getColumnName()] = $columnRegistration;
            }
        }

        return $columnRegistrations;
    }
}

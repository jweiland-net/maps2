<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tca;

final readonly class SynchronizeColumn
{
    public function __construct(
        private ForeignColumn $foreignColumn,
        private string $poiCollectionColumnName,
    ) {}

    public function getForeignColumn(): ForeignColumn
    {
        return $this->foreignColumn;
    }

    public function getPoiCollectionColumnName(): string
    {
        return $this->poiCollectionColumnName;
    }
}

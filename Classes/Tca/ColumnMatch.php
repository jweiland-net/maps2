<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tca;

final readonly class ColumnMatch
{
    public function __construct(
        private string $columnName,
        private string $expr,
        private string $value,
    ) {}

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function getExpr(): string
    {
        return $this->expr;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

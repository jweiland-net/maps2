<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tca;

final readonly class StoragePidLocation
{
    public function __construct(
        private string $extKey,
        private string $property,
        private StoragePidLocationTypeEnum $type = StoragePidLocationTypeEnum::EXTENSION_MANAGER,
    ) {}

    public function getExtKey(): string
    {
        return $this->extKey;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getType(): StoragePidLocationTypeEnum
    {
        return $this->type;
    }
}

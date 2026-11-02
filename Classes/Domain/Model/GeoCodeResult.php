<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Domain\Model;

use JWeiland\Maps2\Client\ClientError;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Result of GeoCodeService::getGeoCodeResult(): the found positions or the
 * error, why the address could not be geocoded.
 */
final readonly class GeoCodeResult
{
    /**
     * @var ObjectStorage<Position>
     */
    public ObjectStorage $positions;

    /**
     * @param ObjectStorage<Position>|null $positions An empty ObjectStorage will be used, if NULL
     */
    public function __construct(
        ?ObjectStorage $positions = null,
        public ?ClientError $error = null,
    ) {
        $this->positions = $positions ?? $this->createEmptyPositions();
    }

    public function getFirstPosition(): ?Position
    {
        foreach ($this->positions as $position) {
            return $position;
        }

        return null;
    }

    public function hasError(): bool
    {
        return $this->error instanceof ClientError;
    }

    /**
     * @return ObjectStorage<Position>
     */
    private function createEmptyPositions(): ObjectStorage
    {
        /** @var ObjectStorage<Position> $positions */
        $positions = new ObjectStorage();

        return $positions;
    }
}

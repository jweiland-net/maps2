<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Mapper;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Each Mapper must have a map Method.
 */
interface MapperInterface
{
    public function map(array $response): ObjectStorage;
}

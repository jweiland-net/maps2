<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Mapper;

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

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Each Mapper must have a map Method.
 */
interface MapperInterface
{
    /**
     * Map response of Map Provider into our Domain Models
     *
     * @param array $response
     * @return ObjectStorage
     */
    public function map(array $response): ObjectStorage;
}

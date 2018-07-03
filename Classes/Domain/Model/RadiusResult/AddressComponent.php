<?php
namespace JWeiland\Maps2\Domain\Model\RadiusResult;

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
 * Domain Model for AddressComponent
 * This class is not part of a local DB table. It's part of the result of a Google Request
 */
class AddressComponent
{
    /**
     * longName
     *
     * @var string
     */
    protected $longName;

    /**
     * shortName
     *
     * @var string
     */
    protected $shortName;

    /**
     * types
     *
     * @var array
     */
    protected $types;

    /**
     * Setter for longName
     *
     * @param string $longName
     */
    public function setLongName($longName)
    {
        $this->longName = $longName;
    }

    /**
     * Getter for LongName
     *
     * @return string
     */
    public function getLongName()
    {
        return $this->longName;
    }

    /**
     * Setter for shortName
     *
     * @param string $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }

    /**
     * Getter for ShortName
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Setter for types
     *
     * @param array $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
    }

    /**
     * Getter for types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }
}

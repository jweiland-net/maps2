<?php
namespace JWeiland\Maps2\Tca\Type;

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
 * Reduce floating values to 6 digits behind point
 */
class FloatType
{
    /**
     * This method returns js code for validating float dataTypes
     *
     * @return string
     */
    public function returnFieldJS()
    {
        return '
            return value;
        ';
    }

    /**
     * This method converts the value into dataType float
     *
     * @param string $value
     * @param string $is_in
     * @param string $set
     *
     * @return string
     */
    public function evaluateFieldValue($value, $is_in, &$set)
    {
        $floatValue = number_format((float)$value, 6);
        return $floatValue;
    }
}

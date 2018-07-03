<?php
namespace JWeiland\Maps2\Controller;

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

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * A controller specialized to handle ajax requests
 */
class AjaxController extends ActionController
{
    /**
     * action ajax
     *
     * @param string $objectName Which Ajax Object has to be called
     * @param array $arguments Arguments which have to be send to the Ajax Object
     *
     * @return string
     */
    public function callAjaxObjectAction($objectName, $arguments = [])
    {
        $className = 'JWeiland\\Maps2\\Ajax\\' . $objectName;
        if (class_exists($className)) {
            $object = $this->objectManager->get($className);
            if (method_exists($object, 'processAjaxRequest')) {
                $result = $object->processAjaxRequest($arguments);
                return $result;
            }
        }
        return '';
    }
}

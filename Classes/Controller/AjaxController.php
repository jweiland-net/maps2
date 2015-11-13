<?php
namespace JWeiland\Maps2\Controller;

/**
 * This file is part of the TYPO3 CMS project.
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
 * Class AjaxController
 *
 * @category Controller
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class AjaxController extends ActionController
{

    /**
     * action ajax
     *
     * @param string $objectName Which Ajax Object has to be called
     * @param array $arguments Arguments which have to be send to the Ajax Object
     * @return string
     */
    public function callAjaxObjectAction($objectName, $arguments = array())
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

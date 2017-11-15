<?php
namespace JWeiland\Maps2\Dispatch;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class AjaxRequest
 *
 * @category Dispatch
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class AjaxRequest
{
    /**
     * objectManager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * contructor of this class
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * dispatcher for ajax requests
     *
     * @return string html content
     */
    public function dispatch()
    {
        // @ToDo generate Pluginnamespace by API-Call
        $parameters = GeneralUtility::_GPmerged('tx_maps2_maps2');

        $className = 'JWeiland\\Maps2\\Ajax\\' . $parameters['objectName'];
        if (class_exists($className)) {
            $object = $this->objectManager->get($className);
            if (method_exists($object, 'processAjaxRequest')) {
                $result = $object->processAjaxRequest($parameters['arguments'], $parameters['hash']);
                return $result;
            }
        }
        return '';
    }
}

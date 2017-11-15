<?php
namespace JWeiland\Maps2\Hook;

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

use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class InitFeSessionHook
 *
 * @category Hook
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class InitFeSessionHook
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * InitFeSessionHook constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager = null)
    {
        if (!$objectManager) {
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        }
        $this->objectManager = $objectManager;
    }

    /**
     * Check GET parameters and allow google requests in session if valid
     *
     * @return void
     */
    public function saveAllowGoogleRequestsInSession()
    {
        /** @var MapService $mapService */
        $mapService = $this->objectManager->get(MapService::class);
        $mapService->explicitAllowGoogleMapRequests();
    }
}

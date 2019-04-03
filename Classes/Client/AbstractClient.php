<?php
namespace JWeiland\Maps2\Client;

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

use JWeiland\Maps2\Client\Request\RequestInterface;
use JWeiland\Maps2\Helper\MessageHelper;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract client to send Requests to Map Providers
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    public function __construct(MessageHelper $messageHelper = null)
    {
        if ($messageHelper === null) {
            $messageHelper = GeneralUtility::makeInstance(MessageHelper::class);
        }
        $this->messageHelper = $messageHelper;
    }

    /**
     * Process Google Maps Requests
     *
     * @param RequestInterface $request
     * @return array
     * @throws \Exception
     */
    public function processRequest(RequestInterface $request)
    {
        if (!$request->isValidRequest()) {
            $this->messageHelper->addFlashMessage('Invalid request: ' . $request->getUri());
            return [];
        }

        try {
            $response = GeneralUtility::getUrl($request->getUri());
            $result = json_decode($response, true);
            if (
                $this->requestHasErrors($result)
                && $GLOBALS['TYPO3_CONF_VARS']['BE']['debug']
            ) {
                DebugUtility::debug($response, 'Response of the Map Providers GeoCode API');
                $result = [];
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 1530698645);
        }

        return $result;
    }

    abstract protected function requestHasErrors($result);
}

<?php
declare(strict_types = 1);
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
use JWeiland\Maps2\Ajax\AjaxInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * A dispatcher class for Ajax Requests
 */
class AjaxRequest
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $response = GeneralUtility::makeInstance(Response::class);

        $postParams = $request->getParsedBody();
        if (!array_key_exists('tx_maps2_maps2', $postParams)) {
            return $response;
        }

        $parameters = $postParams['tx_maps2_maps2'];

        $className = 'JWeiland\\Maps2\\Ajax\\' . $parameters['objectName'];
        if (class_exists($className)) {
            $object = $this->objectManager->get($className);
            if ($object instanceof AjaxInterface) {
                $response->getBody()->write(
                    $object->processAjaxRequest($parameters['arguments'], $parameters['hash'])
                );
                return $response;
            }
        }
        return $response;
    }
}

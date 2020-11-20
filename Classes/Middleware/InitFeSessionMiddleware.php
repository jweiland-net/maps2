<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Middleware;

use JWeiland\Maps2\Configuration\ExtConf;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * We have to save the permission to allow map provider requests before TS-Template rendering.
 * It's needed by our own TS Condition object
 */
class InitFeSessionMiddleware implements MiddlewareInterface
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    public function __construct()
    {
        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);

        // Start SESSION
        // if not in CLI mode
        // if explicitAllowMapProviderRequests in ExtConf is activated
        // if session was not already started
        if (
            !Environment::isCli()
            && $this->extConf->getExplicitAllowMapProviderRequestsBySessionOnly()
            && session_status() === PHP_SESSION_NONE
        ) {
            session_start();
        }
    }

    /**
     * Check GET parameters and allow google requests in session if valid
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parameters = GeneralUtility::_GPmerged('tx_maps2_maps2');
        if (
            isset($parameters['mapProviderRequestsAllowedForMaps2'])
            && (int)$parameters['mapProviderRequestsAllowedForMaps2'] === 1
            && $this->extConf->getExplicitAllowMapProviderRequests()
        ) {
            if (
                $this->extConf->getExplicitAllowMapProviderRequestsBySessionOnly()
                && empty($_SESSION['mapProviderRequestsAllowedForMaps2'])
            ) {
                $_SESSION['mapProviderRequestsAllowedForMaps2'] = 1;
            }

            if (
                !$this->extConf->getExplicitAllowMapProviderRequestsBySessionOnly()
                && (bool)$this->getTypoScriptFrontendController()->fe_user->getSessionData('mapProviderRequestsAllowedForMaps2') === false
            ) {
                $this->getTypoScriptFrontendController()->fe_user->setAndSaveSessionData('mapProviderRequestsAllowedForMaps2', 1);
            }
        }
        return $handler->handle($request);
    }

    /**
     * @return TypoScriptFrontendController|null
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}

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
use JWeiland\Maps2\Helper\MapHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\CookieHeaderTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Save consent in cookie, if requests to map providers was allowed by website visitor
 */
class InitFeSessionMiddleware implements MiddlewareInterface
{
    use CookieHeaderTrait;

    protected ExtConf $extConf;

    protected MapHelper $mapHelper;

    public function __construct(ExtConf $extConf, MapHelper $mapHelper)
    {
        $this->extConf = $extConf;
        $this->mapHelper = $mapHelper;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (
            $this->extConf->getExplicitAllowMapProviderRequests()
            && $this->mapHelper->isRequestToMapProviderAllowed()
        ) {
            $cookie = $this->createCookie($request);
            $response = $response->withAddedHeader('Set-Cookie', $cookie->__toString());
        }

        return $response;
    }

    protected function createCookie(ServerRequestInterface $request): Cookie
    {
        $normalizedParams = $request->getAttribute('normalizedParams');

        // Store consent in COOKIE
        $cookieSameSite = $this->sanitizeSameSiteCookieValue(
            strtolower($GLOBALS['TYPO3_CONF_VARS']['BE']['cookieSameSite'] ?? Cookie::SAMESITE_STRICT)
        );

        // SameSite Cookie = None needs the secure option (only allowed on HTTPS)
        $isSecure = $cookieSameSite === Cookie::SAMESITE_NONE || $normalizedParams->isHttps();

        return new Cookie(
            'mapProviderRequestsAllowedForMaps2',
            '1',
            $this->getCookieExpire(),
            $normalizedParams->getSitePath(),
            null,
            $isSecure,
            false, // Should be false to allow JS based consent tools to delete this cookie
            false,
            $cookieSameSite
        );
    }

    protected function getCookieExpire(): int
    {
        // If COOKIE is activated, set expire to FE sessionDataLifetime which is 1 day by default
        $maxSessionLifetime = $GLOBALS['TYPO3_CONF_VARS']['FE']['sessionDataLifetime'] ?? 60 * 60 * 24;
        $expire = GeneralUtility::makeInstance(Context::class)
                ->getPropertyFromAspect('date', 'timestamp') + $maxSessionLifetime;

        // If session only is activated, $expire = 0 will delete our created COOKIE after closing the browser
        if ($this->extConf->getExplicitAllowMapProviderRequestsBySessionOnly()) {
            $expire = 0;
        }

        return (int)$expire;
    }
}

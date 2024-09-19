<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Helper;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Routing\RouterInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Extract address parts from foreign record array and build an address for Google Maps GoeCode requests
 */
class LinkHelper
{
    protected SiteFinder $siteFinder;

    public function __construct(SiteFinder $siteFinder)
    {
        $this->siteFinder = $siteFinder;
    }

    public function buildUriToCurrentPage(array $parameters, ServerRequestInterface $request): string
    {
        $router = $this->getRouter($request);
        if (!$router instanceof RouterInterface) {
            return '';
        }

        $mergedParameters = [
            '_language' => $request->getAttribute('language'),
        ];

        ArrayUtility::mergeRecursiveWithOverrule($mergedParameters, $parameters);

        return (string)$router->generateUri(
            $this->getCurrentPageUid($request),
            $mergedParameters,
            RouterInterface::ABSOLUTE_URL,
        );
    }

    protected function getRouter(ServerRequestInterface $request): ?RouterInterface
    {
        $site = $this->getCurrentSite($request);
        if (!$site instanceof Site) {
            return null;
        }

        return $site->getRouter();
    }

    protected function getCurrentSite(ServerRequestInterface $request): ?Site
    {
        $site = $request->getAttribute('site');
        if ($site instanceof Site) {
            return $site;
        }

        return null;
    }

    protected function getCurrentPageUid(ServerRequestInterface $request): int
    {
        $pageUid = $this->getCurrentPageUidFromRequest($request);
        if ($pageUid === 0) {
            $pageUid = $this->getCurrentPageUidFromQueryParameters($request);
        }

        return $pageUid;
    }

    protected function getCurrentPageUidFromRequest(ServerRequestInterface $request): int
    {
        $routing = $request->getAttribute('routing');
        if ($routing instanceof PageArguments) {
            return $routing->getPageId();
        }

        return 0;
    }

    protected function getCurrentPageUidFromQueryParameters(ServerRequestInterface $request): int
    {
        $queryParameters = $this->getQueryParameters($request);

        return (int)(array_key_exists('id', $queryParameters) ? $queryParameters['id'] : 0);
    }

    protected function getQueryParameters(ServerRequestInterface $request): array
    {
        if (is_array($request->getQueryParams())) {
            return $request->getQueryParams();
        }

        return [];
    }
}

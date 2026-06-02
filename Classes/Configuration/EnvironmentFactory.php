<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Configuration;

use JWeiland\Maps2\Helper\LinkHelper;
use JWeiland\Maps2\Helper\SettingsHelper;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Factory class to build the Environment configuration state
 */
readonly class EnvironmentFactory
{
    public function __construct(
        private ExtConf $extConf,
        private LinkHelper $linkHelper,
        private SettingsHelper $settingsHelper,
    ) {}

    public function buildEnvironment(
        array $mergedSettingsFromController,
        ServerRequestInterface $request,
    ): Environment {
        return new Environment(
            $this->getPreparedSettings($mergedSettingsFromController, $request),
            ObjectAccess::getGettableProperties($this->extConf),
            $this->getContentRecord($request),
            $this->linkHelper->buildUriToCurrentPage([], $request),
            $this->getCurrentPageUid($request),
            $this->getSiteUrl($request),
        );
    }

    private function getPreparedSettings(array $mergedSettingsFromController, ServerRequestInterface $request): array
    {
        return $this->settingsHelper->getPreparedSettings(
            $this->settingsHelper->restoreTypoScriptDefaultsForEmptyFlexFormSettings(
                $mergedSettingsFromController,
                $request,
            ),
            $request,
        );
    }

    private function getContentRecord(ServerRequestInterface $request): array
    {
        $contentObject = $request->getAttribute('currentContentObject');
        $contentRecord = $contentObject instanceof ContentObjectRenderer ? $contentObject->data : [];
        unset(
            $contentRecord['pi_flexform'],
            $contentRecord['l18n_diffsource'],
        );

        return $contentRecord;
    }

    private function getCurrentPageUid(ServerRequestInterface $request): int
    {
        $routing = $request->getAttribute('routing');
        $pageUid = $routing instanceof PageArguments ? $routing->getPageId() : 0;
        if ($pageUid === 0) {
            $queryParams = $request->getQueryParams();
            $pageUid = (int)($queryParams['id'] ?? 0);
        }

        return $pageUid;
    }

    private function getSiteUrl(ServerRequestInterface $request): string
    {
        /** @var NormalizedParams $normalizedParams */
        $normalizedParams = $request->getAttribute('normalizedParams');

        return $normalizedParams->getSiteUrl();
    }
}

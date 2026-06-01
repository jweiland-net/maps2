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
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Factory class to build the Environment configuration state
 */
final readonly class EnvironmentFactory
{
    public function __construct(
        private ExtConf $extConf,
        private LinkHelper $linkHelper,
        private SettingsHelper $settingsHelper,
    ) {}

    public function buildEnvironment(ServerRequestInterface $request): Environment
    {
        // 1. Get merged and prepared settings
        $mergedSettings = $this->settingsHelper->getMergedSettings();
        $preparedSettings = $this->settingsHelper->getPreparedSettings($mergedSettings);

        // 2. Get gettable properties of ExtConf
        $extConfProperties = ObjectAccess::getGettableProperties($this->extConf);

        // 3. Get content record from the request
        $contentObject = $request->getAttribute('currentContentObject');
        $contentRecord = $contentObject instanceof ContentObjectRenderer ? $contentObject->data : [];
        unset(
            $contentRecord['pi_flexform'],
            $contentRecord['l18n_diffsource'],
        );

        // 4. Build ajax URL using LinkHelper
        $ajaxUrl = $this->linkHelper->buildUriToCurrentPage([], $request);

        // 5. Get current page UID
        $routing = $request->getAttribute('routing');
        $id = $routing instanceof PageArguments ? $routing->getPageId() : 0;
        if ($id === 0) {
            $queryParams = $request->getQueryParams();
            $id = (int)($queryParams['id'] ?? 0);
        }

        return new Environment(
            $preparedSettings,
            $extConfProperties,
            $contentRecord,
            $ajaxUrl,
            $id,
        );
    }
}

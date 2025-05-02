<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\LinkHelper;
use JWeiland\Maps2\Helper\SettingsHelper;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * If you make use of our Partial/PoiCollection or Partial/EditPoiCollection we need some additional information
 * about current record and Extension Settings of maps2. Use this VH to add these additional variables to template.
 */
class GetEnvironmentViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function __construct(
        private readonly SettingsHelper $settingsHelper,
        private readonly LinkHelper $linkHelper,
        private readonly ExtConf $extConf,
    ) {}

    /**
     * Convert all array and object types into a json string. Useful for data-Attributes
     */
    public function render(): string
    {
        $templateVariableContainer = $this->renderingContext->getVariableProvider();
        $extbaseRequest = self::getExtbaseRequest($this->renderingContext);
        if (!$extbaseRequest instanceof RequestInterface) {
            return '';
        }

        $templateVariableContainer->add(
            'environment',
            [
                'settings' => $this->settingsHelper->getPreparedSettings(),
                'extConf' => ObjectAccess::getGettableProperties($this->extConf),
                'ajaxUrl' => $this->linkHelper->buildUriToCurrentPage([], $extbaseRequest),
                'contentRecord' => $extbaseRequest->getAttribute('currentContentObject')->data,
            ],
        );

        $content = $this->renderChildren();

        $templateVariableContainer->remove('environment');

        return $content;
    }

    private function getExtbaseRequest(RenderingContextInterface $renderingContext): ?RequestInterface
    {
        if (
            $renderingContext instanceof RenderingContext
            && $renderingContext->hasAttribute(ServerRequestInterface::class)
            && $renderingContext->getAttribute(ServerRequestInterface::class) instanceof RequestInterface
        ) {
            return $renderingContext->getAttribute(ServerRequestInterface::class);
        }

        return null;
    }
}

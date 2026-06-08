<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers;

use JWeiland\Maps2\Configuration\EnvironmentFactory;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * In the overlay template we need a link to allow requests for map providers.
 * Use this ViewHelper to build that URI.
 */
final class RequestUriForOverlayViewHelper extends AbstractViewHelper
{
    public function __construct(
        private readonly EnvironmentFactory $environmentFactory,
        private readonly UriBuilder $uriBuilder,
    ) {}

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'ttContentUid',
            'int',
            'The tt_content UID of the record which shows the map',
            false,
            0,
        );
    }

    /**
     * Convert all array and object types into a json string. Useful for data-Attributes
     */
    public function render(): string
    {
        $uriBuilder = $this->uriBuilder
            ->reset()
            ->setAddQueryString('untrusted')
            ->setArguments([
                'tx_maps2_maps2' => [
                    'mapProviderRequestsAllowedForMaps2' => 1,
                ],
            ])
            ->setArgumentsToBeExcludedFromQueryString(['cHash']);

        $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);

        $environment = $this->environmentFactory->buildEnvironment([], $request);

        if (($environment->getSettings()['overlay']['link']['addSection'] ?? '') === '1') {
            $ttContentUid = (int)($arguments['ttContentUid'] ?? 0);
            if ($ttContentUid !== 0) {
                $uriBuilder->setSection('c' . $ttContentUid);
            }
        }

        $this->uriBuilder->setRequest($request);

        return $uriBuilder->build();
    }
}

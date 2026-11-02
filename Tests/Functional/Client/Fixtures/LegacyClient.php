<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Client\Fixtures;

use JWeiland\Maps2\Client\AbstractClient;
use JWeiland\Maps2\Configuration\MapProviderEnum;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * Custom client of a third party extension, which was written for maps2
 * before getResponseError() was introduced. It only implements the
 * deprecated checkResponseForErrors() and adds errors to the flash message queue.
 */
readonly class LegacyClient extends AbstractClient
{
    public function canProcess(MapProviderEnum $mapProvider): bool
    {
        return false;
    }

    protected function checkResponseForErrors(?array $processedResponse): void
    {
        if (($processedResponse['status'] ?? '') === 'LEGACY_ERROR') {
            $this->messageHelper->addFlashMessage(
                'Legacy client error',
                'Legacy',
                ContextualFeedbackSeverity::ERROR,
            );
        }
    }
}

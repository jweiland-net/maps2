<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Client;

use JWeiland\Maps2\Client\Request\RequestInterface;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\MessageHelper;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Abstract client to send Requests to Map Providers
 */
abstract readonly class AbstractClient implements ClientInterface
{
    public function __construct(
        protected MessageHelper $messageHelper,
        protected RequestFactory $requestFactory,
        protected ExtConf $extConf,
    ) {}

    public function processRequest(RequestInterface $request, string $address): array
    {
        $processedResponse = [];
        $response = $this->requestFactory->request(
            $request->getUri($this->getPreparedAddress($address)),
        );

        if ($response->getStatusCode() === 200) {
            $processedResponse = \json_decode(
                (string)$response->getBody(),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
            $this->checkResponseForErrors($processedResponse);
        } else {
            $this->messageHelper->addFlashMessage(
                'MapProvider returns a response with a status code different than 200',
                'Client Error',
                ContextualFeedbackSeverity::ERROR,
            );
        }

        if ($this->hasErrors()) {
            return [];
        }

        return $processedResponse;
    }

    public function hasErrors(): bool
    {
        return $this->messageHelper->hasErrorMessages();
    }

    /**
     * @return AbstractMessage[]
     */
    public function getErrors(): array
    {
        return $this->messageHelper->getErrorMessages();
    }

    /**
     * It will also add some additional information like country to address
     */
    protected function getPreparedAddress(string $address): string
    {
        // If address can be interpreted as zip, attach the default country to prevent a worldwide search
        if (
            MathUtility::canBeInterpretedAsInteger($address)
            && $this->extConf->getDefaultCountry() !== ''
        ) {
            $address .= ' ' . $this->extConf->getDefaultCountry();
        }

        return rawurlencode($address);
    }

    abstract protected function checkResponseForErrors(?array $processedResponse): void;
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Event;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Event to render the content of the info window on your own.
 */
class RenderInfoWindowContentEvent
{
    private string $infoWindowContent = '';

    public function __construct(
        private readonly array $poiCollectionRecord,
        private readonly ServerRequestInterface $request,
    ) {}

    public function getPoiCollectionRecord(): array
    {
        return $this->poiCollectionRecord;
    }

    /**
     * Keep in mind: This event was called by a Middleware. The request object is not fully compiled!
     * Attributes like ContentObjectRenderer are not defined until now.
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getInfoWindowContent(): string
    {
        return $this->infoWindowContent;
    }

    public function setInfoWindowContent(string $infoWindowContent): void
    {
        $this->infoWindowContent = $infoWindowContent;
    }
}

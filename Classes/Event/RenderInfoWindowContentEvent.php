<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Event;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Event to render the content of the info window on yourselves
 */
class RenderInfoWindowContentEvent
{
    protected int $poiCollectionUid = 0;

    protected string $infoWindowContent = '';

    protected ?ContentObjectRenderer $contentObjectRenderer = null;

    /**
     * In some rare cases ContentObjectRenderer can be null. So please keep it nullable
     */
    public function __construct(
        int $poiCollectionUid,
        string $infoWindowContent,
        ?ContentObjectRenderer $contentObjectRenderer,
    ) {
        $this->poiCollectionUid = $poiCollectionUid;
        $this->infoWindowContent = $infoWindowContent;
        $this->contentObjectRenderer = $contentObjectRenderer;
    }

    public function getPoiCollectionUid(): int
    {
        return $this->poiCollectionUid;
    }

    public function getInfoWindowContent(): string
    {
        return $this->infoWindowContent;
    }

    public function setInfoWindowContent(string $infoWindowContent): void
    {
        $this->infoWindowContent = $infoWindowContent;
    }

    /**
     * Please test return value against ContentObjectRenderer before using
     */
    public function getContentObjectRenderer(): ?ContentObjectRenderer
    {
        return $this->contentObjectRenderer;
    }
}

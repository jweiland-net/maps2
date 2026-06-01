<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Configuration;

/**
 * Environment configuration state
 */
final readonly class Environment implements \JsonSerializable
{
    /**
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $extConf
     * @param array<string, mixed> $contentRecord
     */
    public function __construct(
        private array $settings,
        private array $extConf,
        private array $contentRecord,
        private string $ajaxUrl,
        private int $id,
    ) {}

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getExtConf(): array
    {
        return $this->extConf;
    }

    public function getContentRecord(): array
    {
        return $this->contentRecord;
    }

    public function getAjaxUrl(): string
    {
        return $this->ajaxUrl;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @throws \ValueError
     */
    public function getMapProvider(): MapProviderEnum
    {
        return MapProviderEnum::from($this->settings['mapProvider']);
    }

    /**
     * Can be used to test if TS template is defined
     */
    public function hasMapProvider(): bool
    {
        try {
            $mapProvider = $this->getMapProvider();
        } catch(\ValueError) {
            return false;
        }

        return true;
    }

    public function isMapRenderable(): bool
    {
        // TS template includes are missing
        if (!$this->hasMapProvider()) {
            return false;
        }

        // Special case for Google Maps. An API key is manatory
        if (
            $this->getMapProvider() === MapProviderEnum::GOOGLE_MAPS
            && ($this->settings['googleMapsApiKey'] ?? '') === ''
        ) {
            return false;
        }

        return true;
    }

    public function jsonSerialize(): array
    {
        return [
            'settings' => $this->settings,
            'extConf' => $this->extConf,
            'contentRecord' => $this->contentRecord,
            'ajaxUrl' => $this->ajaxUrl,
            'id' => $this->id,
        ];
    }
}

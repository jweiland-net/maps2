<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\EventListener\Form;

use JWeiland\Maps2\Form\PoiPosition\PoiPosition;
use JWeiland\Maps2\Form\PoiPosition\PoiPositionMapConfigurator;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Event\BeforeRenderableIsValidatedEvent;

/**
 * Normalizes the submitted value of the form element "Maps2PoiPosition".
 *
 * An array with empty coordinates (marker not placed yet) becomes null, so
 * the NotEmpty validator applies. Other arrays are reduced to the keys
 * "latitude" and "longitude" with trimmed string values.
 */
#[AsEventListener(
    identifier: 'maps2/form/normalize-poi-position',
)]
final readonly class NormalizePoiPositionEventListener
{
    public function __invoke(BeforeRenderableIsValidatedEvent $event): void
    {
        if (!$event->renderable instanceof FormElementInterface
            || $event->renderable->getType() !== PoiPositionMapConfigurator::FORM_ELEMENT_TYPE
        ) {
            return;
        }

        $event->value = $this->normalize($event->value);
    }

    private function normalize(mixed $value): mixed
    {
        if (PoiPosition::isEmptyFormValue($value)) {
            return null;
        }

        return is_array($value) ? $this->normalizeCoordinates($value) : $value;
    }

    /**
     * @param array<int|string, mixed> $value
     * @return array{latitude: string, longitude: string}
     */
    private function normalizeCoordinates(array $value): array
    {
        return [
            PoiPosition::KEY_LATITUDE => $this->normalizeCoordinate($value[PoiPosition::KEY_LATITUDE] ?? ''),
            PoiPosition::KEY_LONGITUDE => $this->normalizeCoordinate($value[PoiPosition::KEY_LONGITUDE] ?? ''),
        ];
    }

    private function normalizeCoordinate(mixed $coordinate): string
    {
        return is_scalar($coordinate) ? trim((string)$coordinate) : '';
    }
}

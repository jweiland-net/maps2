<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Hook\Form;

use JWeiland\Maps2\Form\PoiPosition\PoiPosition;
use JWeiland\Maps2\Form\PoiPosition\PoiPositionMapConfigurator;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Normalizes the submitted value of the form element "Maps2PoiPosition".
 *
 * An array with empty coordinates (marker not placed yet) becomes null, so
 * the NotEmpty validator applies. Other arrays are reduced to the keys
 * "latitude" and "longitude" with trimmed string values.
 */
final readonly class NormalizePoiPositionHook
{
    /**
     * @param array<string, mixed> $requestArguments
     */
    public function afterSubmit(
        FormRuntime $formRuntime,
        RenderableInterface $renderable,
        mixed $elementValue = null,
        array $requestArguments = [],
    ): mixed {
        if (!$renderable instanceof FormElementInterface
            || $renderable->getType() !== PoiPositionMapConfigurator::FORM_ELEMENT_TYPE
        ) {
            return $elementValue;
        }

        if (PoiPosition::isEmptyFormValue($elementValue)) {
            return null;
        }

        return is_array($elementValue) ? $this->normalizeCoordinates($elementValue) : $elementValue;
    }

    /**
     * @param array<int|string, mixed> $elementValue
     * @return array{latitude: string, longitude: string}
     */
    private function normalizeCoordinates(array $elementValue): array
    {
        return [
            PoiPosition::KEY_LATITUDE => $this->normalizeCoordinate($elementValue[PoiPosition::KEY_LATITUDE] ?? ''),
            PoiPosition::KEY_LONGITUDE => $this->normalizeCoordinate($elementValue[PoiPosition::KEY_LONGITUDE] ?? ''),
        ];
    }

    private function normalizeCoordinate(mixed $coordinate): string
    {
        return is_scalar($coordinate) ? trim((string)$coordinate) : '';
    }
}

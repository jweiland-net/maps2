<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Hook\Form;

use JWeiland\Maps2\Form\PoiPosition\PoiPositionMapConfigurator;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Prepares the map of each form element "Maps2PoiPosition", which is rendered
 * on the current page of the form. The template of the element reads the
 * rendering option "maps2PoiPosition". The assets of the map provider are
 * loaded on these pages only.
 *
 * The position is the submitted value or the default value of the element.
 */
final readonly class PoiPositionMapHook
{
    public function __construct(
        private PoiPositionMapConfigurator $poiPositionMapConfigurator,
    ) {}

    public function beforeRendering(FormRuntime $formRuntime, RootRenderableInterface $renderable): void
    {
        if (!$renderable instanceof FormElementInterface
            || $renderable->getType() !== PoiPositionMapConfigurator::FORM_ELEMENT_TYPE
        ) {
            return;
        }

        $renderable->setRenderingOption(
            PoiPositionMapConfigurator::RENDERING_OPTION,
            $this->poiPositionMapConfigurator->configure(
                $renderable,
                $formRuntime[$renderable->getIdentifier()],
                $formRuntime->getRequest(),
                (int)$formRuntime->getCurrentPage()?->getIndex(),
            ),
        );
    }
}

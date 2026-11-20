<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\EventListener\Form;

use JWeiland\Maps2\Form\PoiPosition\PoiPositionMapConfigurator;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Event\BeforeRenderableIsRenderedEvent;

/**
 * Prepares the map of each form element "Maps2PoiPosition", which is rendered
 * on the current page of the form. The template of the element reads the
 * rendering option "maps2PoiPosition" and loads the assets of the map
 * provider with the partial "Maps2PoiPosition/LoadAssets".
 *
 * The position is the value of the form state or the default value of the element.
 */
#[AsEventListener(
    identifier: 'maps2/form/prepare-poi-position-map',
)]
final readonly class PoiPositionMapEventListener
{
    public function __construct(
        private PoiPositionMapConfigurator $poiPositionMapConfigurator,
    ) {}

    public function __invoke(BeforeRenderableIsRenderedEvent $event): void
    {
        $renderable = $event->renderable;
        if (!$renderable instanceof FormElementInterface
            || $renderable->getType() !== PoiPositionMapConfigurator::FORM_ELEMENT_TYPE
        ) {
            return;
        }

        $formRuntime = $event->formRuntime;
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

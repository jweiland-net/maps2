<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Service;

use JWeiland\Maps2\Event\RenderInfoWindowContentEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Service with focus on rendering the info window content of a POI.
 */
readonly class InfoWindowContentService
{
    public function __construct(
        private ViewFactoryInterface $viewFactory,
        private ContentDataProcessor $contentDataProcessor,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function render(array $poiCollectionRecord, ServerRequestInterface $request): string
    {
        $infoWindowContent = $this->getInfoWindowContentFromEventListener($poiCollectionRecord, $request);

        if ($infoWindowContent === '') {
            $infoWindowContent = $this->renderInfoWindowContent($poiCollectionRecord, $request);
        }

        return $infoWindowContent;
    }

    private function renderInfoWindowContent(array $poiCollectionRecord, ServerRequestInterface $request): string
    {
        $siteSettings = $this->getSiteSettings($request);
        if ($siteSettings === []) {
            return '';
        }

        $view = $this->viewFactory->create(new ViewFactoryData(
            partialRootPaths: [$siteSettings['partialRootPath']],
            layoutRootPaths: [$siteSettings['layoutRootPath']],
            templatePathAndFilename: GeneralUtility::getFileAbsFileName(
                $siteSettings['infoWindowContent']['templatePath'],
            ),
            request: $request,
        ));

        $variables = [
            'settings' => $siteSettings,
            'poiCollection' => $poiCollectionRecord,
        ];

        $variables = $this->enrichVariablesWithDataProcessors(
            $variables,
            $this->getContentObjectRenderer($poiCollectionRecord),
        );

        $view->assignMultiple($variables);

        return $view->render();
    }

    private function enrichVariablesWithDataProcessors(
        array $variables,
        ContentObjectRenderer $contentObjectRenderer,
    ): array {
        return $this->contentDataProcessor->process(
            $contentObjectRenderer,
            [
                'dataProcessing.' => [
                    '10' => 'files',
                    '10.' => [
                        'as' => 'infoWindowImages',
                        'references.' => [
                            'fieldName' => 'info_window_images',
                            'table' => 'tx_maps2_domain_model_poicollection',
                        ],
                    ],
                ],
            ],
            $variables,
        );
    }

    /**
     * Get all site settings starting with 'maps2.' like in 'maps2.templateRootPath'.
     * It does not contain any TypoScript!
     */
    private function getSiteSettings(ServerRequestInterface $request): array
    {
        $siteSettings = $this->getCurrentSite($request)->getSettings();

        if (!$siteSettings->has('maps2')) {
            return [];
        }

        return $siteSettings->get('maps2');
    }

    private function getCurrentSite(ServerRequestInterface $request): Site
    {
        return $request->getAttribute('site');
    }

    /**
     * With this EventListener you can render the info window content on your own.
     * For performance reasons we do not work with Fluid Template and ViewHelpers here, that's your work.
     */
    private function getInfoWindowContentFromEventListener(
        array $poiCollectionRecord,
        ServerRequestInterface $request,
    ): string {
        /** @var RenderInfoWindowContentEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new RenderInfoWindowContentEvent(
                $poiCollectionRecord,
                $request,
            ),
        );

        return $event->getInfoWindowContent();
    }

    /**
     * The InfoWindowContentService is called by a Middleware. At that point there is no
     * ContentObjectRenderer in request. So, we have to create a new instance.
     */
    private function getContentObjectRenderer(array $poiCollectionRecord): ContentObjectRenderer
    {
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObjectRenderer->data = $poiCollectionRecord;

        return $contentObjectRenderer;
    }
}

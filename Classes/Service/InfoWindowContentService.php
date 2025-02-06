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
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
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
        private TypoScriptService $typoScriptService,
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
        $maps2TypoScript = $this->getTypoScriptByPath('plugin./tx_maps2.', $request);
        if ($maps2TypoScript === []) {
            return '';
        }

        $settings = $maps2TypoScript['settings.'] ?? [];
        if ($settings === []) {
            return '';
        }

        $siteSettings = $this->getSiteSettings($request);
        if ($siteSettings === []) {
            return '';
        }

        $variables = [
            'settings' => $this->typoScriptService->convertTypoScriptArrayToPlainArray($settings),
            'poiCollection' => $poiCollectionRecord,
        ];

        $variables = $this->enrichVariablesWithDataProcessors(
            $variables,
            $settings['infoWindowContent.']['view.'] ?? [],
            $this->getContentObjectRenderer($poiCollectionRecord),
        );

        $view = $this->createView($maps2TypoScript, $siteSettings, $request);
        $view->assignMultiple($variables);

        return $view->render();
    }

    private function createView(
        array $maps2TypoScript,
        array $siteSettings,
        ServerRequestInterface $request
    ): ViewInterface {
        return $this->viewFactory->create(new ViewFactoryData(
            partialRootPaths: $maps2TypoScript['view.']['partialRootPaths.'],
            layoutRootPaths: $maps2TypoScript['view.']['layoutRootPath.'],
            templatePathAndFilename: GeneralUtility::getFileAbsFileName(
                $siteSettings['infoWindowContent']['templatePath'],
            ),
            request: $request,
        ));
    }

    private function enrichVariablesWithDataProcessors(
        array $variables,
        array $dataProcessingConfiguration,
        ContentObjectRenderer $contentObjectRenderer,
    ): array {
        return $this->contentDataProcessor->process(
            $contentObjectRenderer,
            $dataProcessingConfiguration,
            $variables,
        );
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

    private function getTypoScriptByPath(string $path, ServerRequestInterface $request): array
    {
        try {
            return ArrayUtility::getValueByPath($this->getTypoScriptSetup($request), $path);
        } catch (\RuntimeException|MissingArrayPathException) {
        }

        return [];
    }

    private function getTypoScriptSetup(ServerRequestInterface $request): array
    {
        return $this->getFrontendTypoScript($request)->getSetupArray();
    }

    /**
     * The middleware calling this service is loaded after prepare TSFE, so TypoScript is defined at that point.
     */
    private function getFrontendTypoScript(ServerRequestInterface $request): FrontendTypoScript
    {
        return $request->getAttribute('frontend.typoscript');
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

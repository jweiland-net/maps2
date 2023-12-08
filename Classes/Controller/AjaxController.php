<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller;

use JWeiland\Maps2\Controller\Traits\InjectPoiCollectionRepositoryTrait;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Event\RenderInfoWindowContentEvent;
use JWeiland\Maps2\Service\MapService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Handle Ajax requests of EXT:maps2.
 * Currently, it is used to render the infoWindowContent of POIs.
 * This controller is called by typeNum 1614075471
 */
class AjaxController extends ActionController
{
    use InjectPoiCollectionRepositoryTrait;

    public array $errors = [];

    public function processAction(string $method): ResponseInterface
    {
        $response = [
            'content' => '',
        ];

        if (
            $method === 'renderInfoWindowContent'
            && ($postData = $this->getPostData())
            && array_key_exists('poiCollection', $postData)
        ) {
            $response['content'] = $this->renderInfoWindowContentAction(
                (int)$postData['poiCollection']
            );
        } else {
            $this->errors[] = 'Given method "' . $method . '" is not allowed here.';
        }

        $response['errors'] = $this->errors;

        return $this->jsonResponse(\json_encode($response, JSON_THROW_ON_ERROR));
    }

    protected function getPostData(): array
    {
        try {
            $payload = json_decode((string)$this->request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->errors[] = 'Given post stream does not contain valid JSON string';
            $payload = [];
        }

        return is_array($payload) ? $payload : [];
    }

    public function renderInfoWindowContentAction(int $poiCollectionUid): string
    {
        $poiCollection = $this->poiCollectionRepository->findByIdentifier($poiCollectionUid);
        if (!$poiCollection instanceof PoiCollection) {
            $this->errors[] = sprintf(
                'PoiCollection with UID %d could not be found in AjaxController',
                $poiCollectionUid
            );
            return '';
        }

        $infoWindowContent = $this->emitRenderInfoWindowEvent($poiCollectionUid);
        if ($infoWindowContent === '') {
            $mapService = GeneralUtility::makeInstance(MapService::class);
            $infoWindowContent = $mapService->renderInfoWindow($poiCollection);
        }

        return $infoWindowContent;
    }

    /**
     * With this EventListener you can render the info window content on your own way.
     * For performance reasons we do not work with PoiCollection object here, that's your work.
     * That way you can decide to use fast array by Doctrine or slow (but feature rich)
     * PoiCollection object.
     */
    protected function emitRenderInfoWindowEvent(int $poiCollectionUid): string
    {
        /** @var RenderInfoWindowContentEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new RenderInfoWindowContentEvent(
                $poiCollectionUid,
                '',
                $this->configurationManager->getContentObject()
            )
        );

        return $event->getInfoWindowContent();
    }
}

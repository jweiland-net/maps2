<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Middleware;

use JWeiland\Maps2\Service\InfoWindowContentService;
use JWeiland\Maps2\Service\PoiCollectionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

/**
 * Instead of page type we use Middleware to get the info window content of POIs
 */
readonly class GetInfoWindowContentMiddleware implements MiddlewareInterface
{
    public function __construct(
        private PoiCollectionService $poiCollectionService,
        private InfoWindowContentService $infoWindowContentService,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getHeader('ext-maps2') !== ['infoWindowContent']) {
            return $handler->handle($request);
        }

        $postData = $this->getPostData($request);

        if (isset($postData['error'])) {
            return new JsonResponse($postData);
        }

        if (!array_key_exists('poiCollection', $postData)) {
            return new JsonResponse([
                'error' => 'No POI collection provided',
            ]);
        }

        $poiCollectionUid = (int)$postData['poiCollection'];

        if ($poiCollectionUid === 0) {
            return new JsonResponse([
                'error' => 'POI collection UID can not be empty',
            ]);
        }

        $poiCollectionRecord = $this->poiCollectionService->findByUid($poiCollectionUid, $request);

        if ($poiCollectionRecord === null) {
            return new JsonResponse([
                'error' => 'No POI collection record with requested UID found',
            ]);
        }

        return new JsonResponse([
            'content' => $this->infoWindowContentService->render($poiCollectionRecord, $request),
        ]);
    }

    protected function getPostData(ServerRequestInterface $request): array
    {
        try {
            $payload = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $payload = [
                'errors' => [
                    'Given post stream does not contain valid JSON string',
                ],
            ];
        }

        return is_array($payload) ? $payload : [];
    }
}

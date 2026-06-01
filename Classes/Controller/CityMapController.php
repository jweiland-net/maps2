<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller;

use JWeiland\Maps2\Configuration\Environment;
use JWeiland\Maps2\Configuration\EnvironmentFactory;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * A controller class to show Maps for a pre-configured city
 */
class CityMapController extends ActionController
{
    protected Environment $environment;

    public function __construct(
        protected EnvironmentFactory $environmentFactory,
        protected GeoCodeService $geoCodeService,
    ) {}

    protected function initializeAction(): void
    {
        $this->environment = $this->environmentFactory->buildEnvironment($this->request);
        $this->settings = $this->environment->getSettings();
    }

    protected function initializeView($view): void
    {
        $view->assign('data', $this->environment->getContentRecord());
        $view->assign('environment', $this->environment);
    }

    public function showAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function searchAction(string $street): ResponseInterface
    {
        $position = $this->geoCodeService->getFirstFoundPositionByAddress(
            strip_tags($street) . ' ' . $this->settings['autoAppend'],
        );

        if ($position instanceof Position) {
            $this->view->assign('latitude', $position->getLatitude());
            $this->view->assign('longitude', $position->getLongitude());
            $this->view->assign('address', rawurldecode($street));
        }

        return $this->htmlResponse();
    }
}

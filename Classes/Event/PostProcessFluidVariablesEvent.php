<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Event;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * Post process controller actions that assign fluid variables to view.
 * Often used by controller actions like "overlay" or "show". No redirects are possible here.
 */
class PostProcessFluidVariablesEvent implements ControllerActionEventInterface
{
    public function __construct(
        protected ServerRequestInterface $request,
        protected array $settings,
        protected array $fluidVariables,
    ) {}

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getControllerName(): string
    {
        if ($this->request instanceof Request) {
            return $this->request->getControllerName();
        }

        return '';
    }

    public function getActionName(): string
    {
        if ($this->request instanceof Request) {
            return $this->request->getControllerActionName();
        }

        return '';
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getFluidVariables(): array
    {
        return $this->fluidVariables;
    }

    public function addFluidVariable(string $key, $value): void
    {
        $this->fluidVariables[$key] = $value;
    }
}

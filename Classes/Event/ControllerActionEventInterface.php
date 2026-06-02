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

interface ControllerActionEventInterface
{
    public function getRequest(): ServerRequestInterface;

    /**
     * Get the controller name.
     * It's just "PoiCollection". It's not the full class name.
     */
    public function getControllerName(): string;

    /**
     * Get the action name without appended "Action".
     * It's just "overlay" or "show"
     */
    public function getActionName(): string;

    public function getSettings(): array;
}

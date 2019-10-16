<?php
declare(strict_types = 1);

/*
 * With TYPO3 10.0 new RequestHandlers can only be registered over this file.
 * @ToDo: We have to remove registering RequestHandlers in ext_typoscript_setup.txt in future.
 */
return [
    \JWeiland\Maps2\Mvc\MapProviderOverlayRequestHandler::class
];

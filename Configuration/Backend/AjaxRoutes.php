<?php

/*
 * Definitions for routes provided by EXT:maps2
 * Contains all AJAX-based routes for entry points
 */
return [
    // Store google map routes in database
    'maps2Ajax' => [
        'path' => '/maps2/insert/route',
        'target' => \JWeiland\Maps2\Dispatch\AjaxRequest::class . '::dispatch'
    ],
];

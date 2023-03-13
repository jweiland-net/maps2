<?php
return [
    'frontend' => [
        'jweiland/maps2/initialize-fe-session' => [
            'target' => \JWeiland\Maps2\Middleware\InitFeSessionMiddleware::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
    ],
];

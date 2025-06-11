<?php declare(strict_types=1);

return [
    'frontend' => [
        'Cpsit/EventSubmission/ApiMiddleware' => [
            'target' => Cpsit\EventSubmission\Middleware\ApiMiddleware::class,
            'before' => [
                'typo3/cms-frontend/backend-user-authentication',
                'typo3/cms-adminpanel/sql-logging',
                'typo3/cms-frontend/site'
            ]
        ]
    ]
];

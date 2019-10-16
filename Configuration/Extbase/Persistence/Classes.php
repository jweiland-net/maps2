<?php
declare(strict_types = 1);

/*
 * With TYPO3 10.0 the TypoScript settings persistence.classes has been migrated to this file
 * @ToDo: We have to remove persistence.classes TS in ext_typoscript_setup.txt in future.
 */
return [
    \JWeiland\Maps2\Domain\Model\Category::class => [
        'tableName' => 'sys_category',
        'properties' => []
    ]
];

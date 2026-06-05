<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;

#[AsEventListener(
    identifier: 'maps2:prepareMaps2Relation',
)]
final readonly class PrepareMaps2RelationEventListener
{
    private const TABLE = 'tx_maps2_domain_model_poicollection';

    public function __invoke(AfterTcaCompilationEvent $event): void
    {
        $tca = $event->getTca();

        foreach ($tca as &$tableDefinition) {
            if (!isset($tableDefinition['columns'])) {
                continue;
            }
            if (!is_array($tableDefinition['columns'])) {
                continue;
            }
            foreach ($tableDefinition['columns'] as &$fieldConfig) {
                if (($fieldConfig['config']['type'] ?? '') !== 'group') {
                    continue;
                }

                if (($fieldConfig['config']['renderType'] ?? '') !== 'maps2Relation') {
                    continue;
                }

                if (!isset($fieldConfig['exclude'])) {
                    $fieldConfig['exclude'] = true;
                }

                if (!isset($fieldConfig['label'])) {
                    $fieldConfig['label'] = 'maps2.db:tx_maps2_uid';
                }

                $fieldConfig['config']['allowed'] = self::TABLE;
                $fieldConfig['config']['default'] = 0;
                $fieldConfig['config']['foreign_table'] = self::TABLE;
                $fieldConfig['config']['maxitems'] = 1;
                $fieldConfig['config']['minitems'] = 0;
                $fieldConfig['config']['prepend_tname'] = false;
                $fieldConfig['config']['size'] = 1;
                $fieldConfig['config']['suggestOptions'] = [
                    'default' => [
                        'searchWholePhrase' => true,
                    ],
                ];
            }
        }

        $event->setTca($tca);
    }
}

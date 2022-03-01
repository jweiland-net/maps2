<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2;

use JWeiland\Maps2\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;

/**
 * Update class for the extension manager.
 */
class ext_update
{
    /**
     * Array of flash messages (params) array[][status,title,message]
     *
     * @var array
     */
    protected $messageArray = [];

    /**
     * @var FlexFormTools
     */
    protected $flexFormTools;

    /**
     * Main update function called by the extension manager.
     */
    public function main(): string
    {
        $this->processUpdates();
        return $this->generateOutput();
    }

    /**
     * Called by the extension manager to determine if the update menu entry
     * should by showed.
     */
    public function access(): bool
    {
        $showAccess = false;

        // check for SCA
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tt_content');
        $amountOfRecords = $queryBuilder
            ->count('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'list_type',
                    $queryBuilder->createNamedParameter('maps2_maps2', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->like(
                    'pi_flexform',
                    $queryBuilder->createNamedParameter('%switchableControllerActions%', \PDO::PARAM_STR)
                )
            )
            ->execute()
            ->fetchColumn(0);

        if ((bool)$amountOfRecords) {
            $showAccess = true;
        }

        // check for old marker_icon column in sys_category
        $fields = DatabaseUtility::getColumnsFromTable('sys_category');
        if (array_key_exists('marker_icon', $fields)) {
            $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_category');
            $queryBuilder->getRestrictions()->removeAll()->add(
                GeneralUtility::makeInstance(DeletedRestriction::class)
            );
            $amountOfRecords = $queryBuilder
                ->count('*')
                ->from('sys_category')
                ->where(
                    $queryBuilder->expr()->neq(
                        'marker_icon',
                        $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                    )
                )
                ->execute()
                ->fetchColumn(0);
        }

        if ($amountOfRecords) {
            $showAccess = true;
        }

        return $showAccess;
    }

    /**
     * The actual update function. Add your update task in here.
     */
    protected function processUpdates(): void
    {
        $this->removeSCAFromTtContentRecords();
        $this->migrateMarkerIconToFal();
    }

    /**
     * Remove SwitchableControllerActions from tt_content records as they are not needed anymore
     */
    protected function removeSCAFromTtContentRecords(): void
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tt_content');
        $rows = $queryBuilder
            ->select('uid', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'list_type',
                    $queryBuilder->createNamedParameter('maps2_maps2', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->like(
                    'pi_flexform',
                    $queryBuilder->createNamedParameter('%switchableControllerActions%', \PDO::PARAM_STR)
                )
            )
            ->execute()
            ->fetchAll();

        if (is_array($rows)) {
            $affectedRows = 0;
            foreach ($rows as $row) {
                $flexFormFields = GeneralUtility::xml2array($row['pi_flexform']);
                if (isset($flexFormFields['data']['sDEFAULT']['lDEF']['switchableControllerActions'])) {
                    unset($flexFormFields['data']['sDEFAULT']['lDEF']['switchableControllerActions']);
                }

                $connection = $this->getConnectionPool()->getConnectionForTable('tt_content');
                $affectedRows += $connection->update(
                    'tt_content',
                    [
                        'pi_flexform' => $this->getFlexFormTools()->flexArray2Xml($flexFormFields)
                    ],
                    [
                        'uid' => (int)$row['uid']
                    ]
                );
            }

            $this->messageArray[] = [
                FlashMessage::OK,
                'Update records successful',
                sprintf(
                    'We have updated %d of %d tt_content records',
                    $affectedRows,
                    count($rows)
                )
            ];
        } else {
            $this->messageArray[] = [
                FlashMessage::ERROR,
                'Error while selecting tt_content records',
                'SQL-Error'
            ];
        }
    }

    /**
     * Migrate old marker icon of sys_category to FAL
     */
    protected function migrateMarkerIconToFal(): void
    {
        // check for old marker_icon column in sys_category first
        $fields = DatabaseUtility::getColumnsFromTable('sys_category');
        if (array_key_exists('marker_icon', $fields)) {
            $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_category');
            $queryBuilder->getRestrictions()->removeAll()->add(
                GeneralUtility::makeInstance(DeletedRestriction::class)
            );
            $sysCategories = $queryBuilder
                ->select('uid', 'pid', 'marker_icon')
                ->from('sys_category')
                ->where(
                    $queryBuilder->expr()->neq(
                        'marker_icon',
                        $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                    )
                )
                ->execute()
                ->fetchAll();
            if (is_array($sysCategories)) {
                foreach ($sysCategories as $sysCategory) {
                    try {
                        $file = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                            \TYPO3\CMS\Core\Resource\ResourceFactory::class
                        )->retrieveFileOrFolderObject($sysCategory['marker_icon']);

                        if ($file instanceof FileInterface) {
                            // Assemble DataHandler data
                            $newId = 'NEW1234';
                            $data = [];
                            $data['sys_file_reference'][$newId] = [
                                'table_local' => 'sys_file',
                                'uid_local' => $file->getUid(),
                                'tablenames' => 'sys_category',
                                'uid_foreign' => $sysCategory['uid'],
                                'fieldname' => 'maps2_marker_icons',
                                'pid' => $sysCategory['pid']
                            ];
                            $data['sys_category'][$sysCategory['uid']] = [
                                'maps2_marker_icons' => $newId
                            ];
                            // Get an instance of the DataHandler and process the data
                            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
                            $dataHandler->start($data, []);
                            $dataHandler->process_datamap();
                        }
                    } catch (\Exception $exception) {
                        // file does not exist or whatever
                    }

                    // remove old icon
                    $connection = $this->getConnectionPool()->getConnectionForTable('sys_category');
                    $connection->update(
                        'sys_category',
                        [
                            'marker_icon' => ''
                        ],
                        [
                            'uid' => (int)$sysCategory['uid']
                        ]
                    );
                }

                $this->messageArray[] = [
                    FlashMessage::OK,
                    'Migration successful',
                    sprintf(
                        'We have magrated %d sys_category records to FAL',
                        count($sysCategories)
                    )
                ];
            }
        }
    }

    /**
     * Generates output by using flash messages
     */
    protected function generateOutput(): string
    {
        $output = '';
        foreach ($this->messageArray as $messageItem) {
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $messageItem[2],
                $messageItem[1],
                $messageItem[0]);

            $output .= GeneralUtility::makeInstance(FlashMessageRendererResolver::class)
                ->resolve()
                ->render([$flashMessage]);
        }

        return $output;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

    protected function getFlexFormTools(): FlexFormTools
    {
        if (!$this->flexFormTools instanceof FlexFormTools) {
            $this->flexFormTools = GeneralUtility::makeInstance(FlexFormTools::class);
        }

        return $this->flexFormTools;
    }
}

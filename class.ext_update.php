<?php
namespace JWeiland\Maps2;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Messaging\FlashMessage;
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
    protected $messageArray = array();

    /**
     * @var FlexFormTools
     */
    protected $flexFormTools;

    /**
     * Main update function called by the extension manager.
     *
     * @return string
     */
    public function main()
    {
        $this->processUpdates();
        return $this->generateOutput();
    }

    /**
     * Called by the extension manager to determine if the update menu entry
     * should by showed.
     *
     * @return bool
     */
    public function access()
    {
        $amountOfRecords = $this->getDatabaseConnection()->exec_SELECTcountRows(
            '*',
            'tt_content',
            'list_type=\'maps2_maps2\'' .
            'AND pi_flexform LIKE \'%switchableControllerActions%\''
        );
        return (bool)$amountOfRecords;
    }

    /**
     * The actual update function. Add your update task in here.
     *
     * @return void
     */
    protected function processUpdates()
    {
        $this->removeSCAFromTtContentRecords();
    }

    /**
     * Remove SwitchableControllerActions from tt_content records as they are not needed anymore
     *
     * @return void
     */
    protected function removeSCAFromTtContentRecords()
    {
        $rows = $this->getDatabaseConnection()->exec_SELECTgetRows(
            'uid, pi_flexform',
            'tt_content',
            'list_type=\'maps2_maps2\'' .
            'AND pi_flexform LIKE \'%switchableControllerActions%\''
        );
        if (!empty($rows)) {
            $affectedRows = 0;
            foreach ($rows as $row) {
                $flexFormFields = GeneralUtility::xml2array($row['pi_flexform']);
                if (isset($flexFormFields['data']['sDEFAULT']['lDEF']['switchableControllerActions'])) {
                    unset($flexFormFields['data']['sDEFAULT']['lDEF']['switchableControllerActions']);
                }
                $this->getDatabaseConnection()->exec_UPDATEquery(
                    'tt_content',
                    'uid=' . (int)$row['uid'],
                    array(
                        'pi_flexform' => $this->getFlexFormTools()->flexArray2Xml($flexFormFields)
                    )
                );
                $affectedRows += $this->getDatabaseConnection()->sql_affected_rows();
            }
            $this->messageArray[] = array(
                FlashMessage::OK,
                'Update records successful',
                sprintf(
                    'We have updated %d of %d tt_content records',
                    (int)$affectedRows,
                    count($rows)
                )
            );
        } else {
            $this->messageArray[] = array(
                FlashMessage::ERROR,
                'Error while selecting tt_content records',
                'SQL-Error: ' . $this->getDatabaseConnection()->sql_error()
            );
        }
    }


    /**
     * Generates output by using flash messages
     *
     * @return string
     */
    protected function generateOutput()
    {
        $output = '';
        foreach ($this->messageArray as $messageItem) {
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                $messageItem[2],
                $messageItem[1],
                $messageItem[0]);
            if (GeneralUtility::compat_version('8.0')) {
                $output .= $flashMessage->getMessageAsMarkup();
            } else {
                $output .= $flashMessage->render();
            }
        }
        return $output;
    }

    /**
     * Get TYPO3s Database Connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * Get TYPO3s FlexFormTools
     *
     * @return FlexFormTools
     */
    protected function getFlexFormTools()
    {
        if (!$this->flexFormTools instanceof FlexFormTools) {
            $this->flexFormTools = GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Core\\Configuration\\FlexForm\\FlexFormTools'
            );
        }
        return $this->flexFormTools;
    }
}

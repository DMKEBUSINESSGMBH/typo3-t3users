<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2009 Rene Nitzsche (dev@dmk-ebusiness.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Service for logging feuser actions.
 *
 * @author Rene Nitzsche
 */
class tx_t3users_services_logging extends \TYPO3\CMS\Core\Service\AbstractService
{
    /**
     * TYPO3 Login of feuser.
     *
     * @param int $feuserUid
     */
    public function logLogin($feuserUid)
    {
        $log = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_models_log', ['feuser' => $feuserUid, 'typ' => 'LOGIN']);
        $this->writeLog($log);
    }

    /**
     * TYPO3 Logout of feuser.
     *
     * @param int $feuserUid
     */
    public function logLogout($feuserUid)
    {
        $log = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_models_log', ['feuser' => $feuserUid, 'typ' => 'LOGOUT']);
        $this->writeLog($log);
    }

    /**
     * Write a log entry to database.
     *
     * @param tx_t3users_models_ILog $log
     */
    public function writeLog(tx_t3users_models_ILog $log)
    {
        $row['tstamp'] = $log->getTimeStamp() ? $log->getTimeStamp() : date('Y-m-d H:i:s', time());
        $row['feuser'] = $log->getFEUserUid();
        if (0 == intval($row['feuser'])) {
            // Prüfen, ob aktuell ein User vorhanden ist
            $feuser = tx_t3users_models_feuser::getCurrent();
            $row['feuser'] = is_object($feuser) ? $feuser->getUid() : 0;
        }
        if (is_object($GLOBALS['BE_USER'])) {
            $row['beuser'] = $GLOBALS['BE_USER']->user['uid'];
        }
        $row['typ'] = $log->getType();
        $row['recuid'] = $log->getRecUid();
        $row['rectable'] = $log->getRecTable();
        // we support strings and arrays
        $data = $log->getData();
        $data = (is_array($data)) ? serialize($data) : $data;
        $row['data'] = trim($data);
        \Sys25\RnBase\Database\Connection::getInstance()->doInsert('tx_t3users_log', $row, 0);
    }

    /**
     * Search database for teams.
     *
     * @param array $fields
     * @param array $options
     *
     * @return array of tx_t3users_models_feuser
     */
    public function search($fields, $options)
    {
        $searcher = \Sys25\RnBase\Search\SearchBase::getInstance('tx_t3users_search_log');
        $options['enablefieldsoff'] = 1;

        return $searcher->search($fields, $options);
    }
}

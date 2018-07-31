<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2018 Rene Nitzsche (dev@dmk-ebusiness.de)
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

tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_rnbase_util_DB');
tx_rnbase::load('tx_rnbase_parameters');
tx_rnbase::load('Tx_Rnbase_Utility_T3General');

/**
 * Übernahme von FEUser-Sessions.
 * Ein BE-Account hat die Möglichkeit sich ohne Kenntnis des Passworts den Account eines beliebigen FE-Users
 * in seine eigene PHP-Session zu übernehmen.
 */
class tx_t3users_util_LoginAsFEUser
{
    /**
     * Wenn der BE-Mitarbeiter im Frontend als bestimmter Nutzer angemeldet werden soll,
     * dann wird das hier geprüft. Außerdem wird ein Hidden-Field gesetzt, daß die UID des FE-Users
     * aufnimmt.
     */
    public static function hijackUser($feuserid = 0, $redirectUrl = '/')
    {
        $ret = '';
        if (!$feuserid) {
            $userData = tx_rnbase_parameters::getPostOrGetParameter('hijack');
            if (is_array($userData)) {
                list($feuserid, ) = each($userData);
            }
            $feuserid = intval($feuserid);
        }
        if (!$feuserid) {
            return $ret;
        }

        $fesession = $_COOKIE['fe_typo_user'];
        if ($fesession) {
            // Der User hat oder hatte schon eine FE-Session
            // Liegt ein Datensatz in der DB?
            $current = self::getCurrentFeUserSession($fesession);
            if ($current) {
                self::updateFeUserSession($fesession, $feuserid);
            } else {
                self::createFeUserSession($fesession, $feuserid);
            }
        } else {
            self::createFeUserSession($fesession, $feuserid);
        }
        $ret .= '
        <script>
        window.open("'.$redirectUrl.'");
        </script>
        ';

        return $ret;
    }
    /**
     * Aktualisiert die User-Session in der DB.
     *
     * @param string $fesession
     * @param int $feuserid
     */
    private static function updateFeUserSession($fesessionId, $feuserid)
    {
        $where = 'ses_id = %1$s';
        $where .= tx_rnbase_util_TYPO3::isTYPO87OrHigher() ? '' : ' AND fe_sessions.ses_name = \'fe_typo_user\' ';
        $where = sprintf($where, $GLOBALS['TYPO3_DB']->fullQuoteStr($fesessionId, 'fe_sessions'));
        $values = array('ses_userid' => $feuserid , 'ses_tstamp' => $GLOBALS['EXEC_TIME']);
        tx_rnbase_util_DB::doUpdate('fe_sessions', $where, $values, 0);
    }

    private static function createFeUserSession($fesessionId, $feuserid)
    {
        if (!$fesessionId) {
            // Es muss eine neue FE-Usersession angelegt werden
            $hash_length = 10;
            $fesessionId = substr(md5(uniqid('').getmypid()), 0, $hash_length);
            $cookieDomain = $GLOBALS[TYPO3_CONF_VARS]['SYS']['cookieDomain'];
            SetCookie('fe_typo_user', $fesessionId, 0, '/', $cookieDomain ? $cookieDomain : '');
        }
        $values = self::getNewSessionRecord($fesessionId, $feuserid);
        tx_rnbase_util_DB::doInsert('fe_sessions', $values, 0);
    }

    private static function getNewSessionRecord($sessionId, $userId)
    {
        $abstractUserAuthenticationClass = tx_rnbase_util_Typo3Classes::getAbstractUserAuthenticationClass();
        $frontendUserAuthenticationClass = tx_rnbase_util_Typo3Classes::getFrontendUserAuthenticationClass();
        if (!is_callable(array($abstractUserAuthenticationClass, 'ipLockClause_remoteIPNumber'))) {
            // Ab 4.5 ist die Methode nicht mehr public. Daher den notwendigen
            // Record anders erstellen
            $auth = tx_rnbase::makeInstance($frontendUserAuthenticationClass);
            $auth->id = $sessionId;
            $auth->is_permanent = true;
            $auth->name = 'fe_typo_user';
            $auth->userid_column = 'uid';
            $auth->is_permanent = 0;
            $auth->lockIP = $GLOBALS['TYPO3_CONF_VARS']['FE']['lockIP'];
            $tempUser = array($auth->userid_column => $userId);

            return $auth->getNewSessionRecord($tempUser);
        }

        $record = array(
            'ses_id' => $sessionId,
            'ses_iplock' => $frontendUserAuthenticationClass::ipLockClause_remoteIPNumber($GLOBALS['TYPO3_CONF_VARS']['FE']['lockIP']),
            'ses_hashlock' => Tx_Rnbase_Utility_T3General::md5int(':'.tx_rnbase_util_Misc::getIndpEnv('HTTP_USER_AGENT')) , //$this->hashLockClause_getHashInt(),
            'ses_userid' => $userId,
            'ses_tstamp' => $GLOBALS['EXEC_TIME']
        );

        if (!tx_rnbase_util_TYPO3::isTYPO87OrHigher()) {
            $record['ses_name'] = 'fe_typo_user';
        }

        return $record;
    }

    /**
     * Prüft, ob für die SessionId eine User-Session in der Datenbank liegt
     * @param string $fesessionId
     * @return array
     */
    protected static function getCurrentFeUserSession($fesessionId)
    {
        $where = 'ses_id = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($fesessionId, 'fe_sessions');
        $where .= tx_rnbase_util_TYPO3::isTYPO87OrHigher() ? '' : ' AND fe_sessions.ses_name = \'fe_typo_user\' ';
        $options = ['where' => $where];
        $options['enablefieldsoff'] = 1;
        $result = Tx_Rnbase_Database_Connection::getInstance()->doSelect('*', 'fe_sessions', $options, 0);

        return count($result) ? $result[0] : false;
    }
}

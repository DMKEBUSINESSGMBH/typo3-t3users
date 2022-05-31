<?php

use Sys25\RnBase\Utility\TYPO3;

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
            $userData = \Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('hijack');
            if (is_array($userData)) {
                $feuserid = current($userData);
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
            if (self::isPersistedFeUserSession($fesession)) {
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
        $values = ['ses_userid' => $feuserid, 'ses_tstamp' => $GLOBALS['EXEC_TIME']];
        \TYPO3\CMS\Core\Session\UserSessionManager::create('login')->updateSessionTimestamp(
            \TYPO3\CMS\Core\Session\UserSession::createFromRecord($fesessionId, $values)
        );
    }

    private static function createFeUserSession($fesessionId, $feuserid)
    {
        if (!$fesessionId) {
            // Es muss eine neue FE-Usersession angelegt werden
            $hash_length = 10;
            $fesessionId = substr(md5(uniqid('').getmypid()), 0, $hash_length);
            $cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];
            setcookie('fe_typo_user', $fesessionId, 0, '/', $cookieDomain ?? '');
        }
        \TYPO3\CMS\Core\Session\UserSessionManager::create('login')->elevateToFixatedUserSession(
            \TYPO3\CMS\Core\Session\UserSession::createNonFixated($fesessionId),
            $feuserid
        );
    }

    /**
     * Prüft, ob für die SessionId eine User-Session in der Datenbank liegt.
     *
     * @param string $fesessionId
     */
    protected static function isPersistedFeUserSession($fesessionId): bool
    {
        return \TYPO3\CMS\Core\Session\UserSessionManager::create('login')->isSessionPersisted(
            \TYPO3\CMS\Core\Session\UserSession::createNonFixated($fesessionId)
        );
    }
}

<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_DB');

/**
 * Übernahme von FEUser-Sessions.
 * Ein BE-Account hat die Möglichkeit sich ohne Kenntnis des Passworts den Account eines beliebigen FE-Users
 * in seine eigene PHP-Session zu übernehmen.
 */
class tx_t3users_util_LoginAsFEUser {
	/**
	 * Wenn der BE-Mitarbeiter im Frontend als bestimmter Nutzer angemeldet werden soll,
	 * dann wird das hier geprüft. Außerdem wird ein Hidden-Field gesetzt, daß die UID des FE-Users
	 * aufnimmt.
	 */
	public static function hijackUser($feuserid=0, $redirectUrl='/')	{
//		$ret = '
//<input type="hidden" name="hijack">
//<script>
//	function hijack(feuser) {
//	  document.forms[0].hijack.value = feuser;
//	  document.forms[0].submit();
//	}
//</script>
//		'.chr(10);
		$ret = '';
		if(!$feuserid) {
			$userData = t3lib_div::_GP('hijack');
			if(is_array($userData))
				list($feuserid, ) = each($userData);
			$feuserid = intval($feuserid);
		}
		if(!$feuserid) return $ret;

		$fesession = $_COOKIE['fe_typo_user'];
		if($fesession) {
			// Der User hat oder hatte schon eine FE-Session
			// Liegt ein Datensatz in der DB?
			$current = self::getCurrentFeUserSession($fesession);
			if($current) {
				self::updateFeUserSession($fesession, $feuserid);
			}
			else
				self::createFeUserSession($fesession, $feuserid);
		}
		else
			self::createFeUserSession($fesession, $feuserid);
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
	function updateFeUserSession($fesessionId, $feuserid) {
    $where = 'ses_id = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($fesessionId, 'fe_sessions').'
							AND fe_sessions.ses_name = \'fe_typo_user\' ';
    $values = array('ses_userid' => $feuserid , 'ses_tstamp' => $GLOBALS['EXEC_TIME']);
    tx_rnbase_util_DB::doUpdate('fe_sessions',$where,$values,0);
	}
	function createFeUserSession($fesessionId, $feuserid) {
		if(!$fesessionId) {
			// Es muss eine neue FE-Usersession angelegt werden
			$hash_length = 10;
			$fesessionId = substr(md5(uniqid('').getmypid()),0,$hash_length);
			$cookieDomain = $GLOBALS[TYPO3_CONF_VARS]['SYS']['cookieDomain'];
			SetCookie('fe_typo_user', $fesessionId, 0, '/', $cookieDomain ? $cookieDomain : '');
		}
		$values = self::getNewSessionRecord($fesessionId, $feuserid);
    tx_rnbase_util_DB::doInsert('fe_sessions',$values,0);
	}
	function getNewSessionRecord($sessionId, $userId) {

		if(!is_callable(array('t3lib_userAuth', 'ipLockClause_remoteIPNumber'))) {
			// Ab 4.5 ist die Methode nicht mehr public. Daher den notwendigen
			// Record anders erstellen
			$auth = tx_rnbase::makeInstance('t3lib_userAuth');
			$auth->id = $sessionId;
			$auth->name = 'fe_typo_user';
			$auth->userid_column = 'uid';
			$auth->lockIP = $GLOBALS['TYPO3_CONF_VARS']['FE']['lockIP'];
			$tempUser = array($auth->userid_column => $userId);

			return $auth->getNewSessionRecord($tempUser);
		}

		return array(
			'ses_id' => $sessionId,
			'ses_name' => 'fe_typo_user',
			'ses_iplock' => t3lib_userAuth::ipLockClause_remoteIPNumber($GLOBALS['TYPO3_CONF_VARS']['FE']['lockIP']),
			'ses_hashlock' => t3lib_div::md5int(':'.t3lib_div::getIndpEnv('HTTP_USER_AGENT')) , //$this->hashLockClause_getHashInt(),
			'ses_userid' => $userId,
			'ses_tstamp' => $GLOBALS['EXEC_TIME']
		);
	}

	/**
	 * Prüft, ob für die SessionId eine User-Session in der Datenbank liegt
	 * @param string $fesessionId
	 * @return array
	 */
	function getCurrentFeUserSession($fesessionId) {
    $options['where'] = 'ses_id = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($fesessionId, 'fe_sessions').'
							AND fe_sessions.ses_name = \'fe_typo_user\' ';
    $options['enablefieldsoff'] = 1;
    $result = tx_rnbase_util_DB::doSelect('*', 'fe_sessions', $options, 0);
    return count($result) ? $result[0] : false;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/util/class.tx_t3users_util_LoginAsFEUser.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/util/class.tx_t3users_util_LoginAsFEUser.php']);
}


?>

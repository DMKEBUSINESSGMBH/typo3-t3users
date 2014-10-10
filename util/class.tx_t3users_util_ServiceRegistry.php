<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * Zentrale Klasse fuer den Zugriff auf verschiedene Services
 */
class tx_t3users_util_ServiceRegistry {

	/**
	 * Liefert den FE-User-Service
	 * @return tx_t3users_services_feuser
	 */
	public static function getFeUserService() {
		return tx_rnbase_util_Misc::getService('t3users', 'feuser');
	}
	/**
	 * Service zur Verwaltung der Registrierung
	 * @return tx_t3users_services_feuser
	 */
	public static function getRegistrationService() {
		return tx_rnbase_util_Misc::getService('t3users', 'registration');
	}
	/**
	 * Liefert den Logging-Service
	 * @return tx_t3users_services_logging
	 */
	public static function getLoggingService() {
		return tx_rnbase_util_Misc::getService('t3users', 'logging');
	}
	/**
	 * Liefert den E-Mail-Service
	 * @return tx_t3users_services_email
	 */
	public static function getEmailService() {
		return tx_rnbase_util_Misc::getService('t3users', 'email');
	}
	/**
	 * Liefert den LoginForm-Service
	 * @return tx_t3users_services_LoginForm
	 */
	public static function getLoginFormService() {
		return tx_rnbase_util_Misc::getService('t3users', 'loginform');
	}
}
if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/util/class.tx_t3users_util_ServiceRegistry.php'])	{
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/util/class.tx_t3users_util_ServiceRegistry.php']);
}
?>
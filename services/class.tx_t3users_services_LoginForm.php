<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2013 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_t3users_search_builder');
tx_rnbase::load('tx_t3users_exceptions_User');



/**
 * Service to extend login form
 *
 * @author Rene Nitzsche
 */
class tx_t3users_services_LoginForm extends t3lib_svbase {

	/**
	 * Aufgabe ist es, das Login-Formular so zu erweitern, daß es erfolgreich abgeschickt wird.
	 * Dazu muss eine onSubmit Funktion geliefert werden. Zusätzlich sind für die Verschlüsselung
	 * der Zugangsdaten ggf. weitere LoginFelder und zusätzlicher JS-Code notwendig.
	 *
	 * @param stdClass $code
	 * @param tx_t3users_actions_Login $plugin
	 * @return array
	 */
	public function extendLoginForm($code, $statusKey, $configurations, $confId, $plugin) {
		// Einfachste Form ist ohne alles
		$method = strtolower($configurations->get($confId.'extend.method'));
		if($method == 'none')
			return;

		if($method == 'auto') {
			$usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
			if($usrSrv->useRSA()) {
				$method = 'rsa';
			}
			elseif($usrSrv->useMD5()) {
				$method = 'md5';
			}
			else {
				$method = 'nomd5';
			}
		}


		$data = $configurations->get($confId.'extend.'.$method.'.');
		$keys = array('formFields', 'jsFiles', 'jsCode', 'onsubmit');
		foreach ($keys As $key)
			if(isset($data[$key]))
			$code->$key = $data[$key];

		$methodName = 'handleMethod_'.$method;
		if(method_exists($this, $methodName))
			$this->$methodName($code, $statusKey, $configurations, $confId, $plugin);
	}

	/**
	 * Prepare form for usage with kb_md5fepw extension.
	 * @param stdClass $code
	 * @param string $statusKey
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 * @param tx_t3users_actions_Login $plugin
	 */
	protected function handleMethod_md5($code, $statusKey, $configurations, $confId, $plugin) {
		$code->jsFiles = '<script language="JavaScript" type="text/javascript" src="typo3/md5.js"></script>';
		$chal_val = md5(time().getmypid().uniqid());
		tx_rnbase::load('tx_rnbase_util_DB');
		tx_rnbase_util_DB::doInsert('tx_kbmd5fepw_challenge', array('challenge' => $chal_val, 'tstamp' => time()), 0);

		$code->formFields = '<input type="hidden" name="challenge" value="'.$chal_val.'">';
		$code->onsubmit = 'superchallenge_password_md5(form)';
	}

	/**
	 * Prepare form for usage with rsa security level.
	 * @param stdClass $code
	 * @param string $statusKey
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 * @param tx_t3users_actions_Login $plugin
	 */
	protected function handleMethod_rsa($code, $statusKey, $configurations, $confId, $plugin) {
		require_once(t3lib_extMgm::extPath('rsaauth') . 'hooks/class.tx_rsaauth_feloginhook.php');

		$rsa = tx_rnbase::makeInstance('tx_rsaauth_feloginhook');
		$result = $rsa->loginFormHook();
		// Use onSubmit only if not set by Typoscript
		if(!$code->onsubmit)
			$code->onsubmit = $result[0];
		// Der Hook liefert den JS-Code und die Formularfelder zusammen.
		// Für t3users muss das getrennt werden
		$mixedCode = $result[1];
		$code->formFields = strstr($mixedCode, '<input');
		$code->jsFiles = strstr($mixedCode, '<input', true);

	}
}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/services/class.tx_t3users_services_LoginForm.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/services/class.tx_t3users_services_LoginForm.php']);
}

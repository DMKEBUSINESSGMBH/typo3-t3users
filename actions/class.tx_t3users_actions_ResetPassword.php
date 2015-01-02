<?php
/**
 * 	@package TYPO3
 *  @subpackage tx_t3users
 *  @author René Nitzsche <nitzsche@das-medienkombinat.de>
 *
 *  Copyright notice
 *
 *  (c) 2013 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
 */

/**
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_action_BaseIOC');

/**
 * Reset des Passworts eines Users.
 */
class tx_t3users_actions_ResetPassword extends tx_rnbase_action_BaseIOC {

	/**
	 *
	 * @param tx_rnbase_parameters $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewData
	 *
	 * @return string error msg or null
	 */
	protected function handleRequest(&$parameters,&$configurations, &$viewdata){
		$confirmstring = htmlspecialchars($parameters->get('confirm'));
		$uid = $parameters->getInt('uid');
		$viewdata->offsetSet('linkparams', array('confirm'=>$confirmstring, 'uid'=>$uid));

		// Confirm prüfen
		$usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
		$feuser = $usrSrv->getUserForConfirm($uid, $confirmstring);
		if(!$feuser) {
			$status = 'CONFIRMFAILED';
		}
		else {
			$status = 'FORM';
			$pass1 = htmlspecialchars($parameters->get('pass1'));
			$pass2 = htmlspecialchars($parameters->get('pass2'));

			if ($pass1) {
				$validated = ($pass1 && $pass1 == $pass2);
				$validationFailureMessage = '###LABEL_WRONG_PASS###';
				// Hook für weitere Validierungen
				tx_rnbase_util_Misc::callHook(
					't3users','resetPassword_ValidatePassword',
					array(
						'validated' => &$validated,
						'validationFailureMessage' => &$validationFailureMessage,
						'password' => $pass1
					),
					$this
				);
				if ($validated) {
					// Speichern
					$usrSrv->saveNewPassword($feuser, $pass1);
					// Und TODO: Redirect...
					$status = 'FINISHED';
				}
				else {
					// Validierung fehlgeschlagen
					$viewdata->offsetSet('message', $validationFailureMessage);
				}
			}
		}
		$viewdata->offsetSet('subpart', $status);
		return '';
	}

	/**
	 * @return string
	 */
	public function getTemplateName() { return 'resetpassword';}

	/**
	 * @return string
	 */
	public function getViewClassName() { return 'tx_t3users_views_ResetPassword';}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_ResetPassword.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_ResetPassword.php']);
}

?>

<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_action_BaseIOC');
tx_rnbase::load('tx_t3users_models_feuser');


/**
 * Controller für die Anforderung der Bestätigungsmail für eine Neuregistrierung
 * TODO: Ist das hier sinnvoll aufgehoben? Was macht das genau?
 */
class tx_t3users_actions_RequestConfirmEmail extends tx_rnbase_action_BaseIOC {

	/**
	 *
	 * @param tx_rnbase_IParameters $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param ArrayObject $viewData
	 */
	function handleRequest(&$parameters,&$configurations, &$viewData){
		global $TSFE;
		$confirm = $parameters->get('confirm');
		if(!$confirm) return '<!-- -->';
			
		// User wants to be confirmed
		$userUid = $parameters->getInt('uid');

		// Load instance
		$feuser = tx_t3users_models_feuser::getInstance($userUid);
		$usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();

		// Set config
		$options['successgroupsadd'] = $configurations->get($this->getConfId().'userGroupAfterConfirmation');
		$options['successgroupsremove'] = $configurations->get($this->getConfId().'userGroupUponRegistration');
		
		$confirmationMailSent = $usrSrv->confirmUser($feuser, $confirm, $options);
		if($confirmed) {
			$viewData->offsetSet('part', 'CONFIRMED');
			$viewData->offsetSet('feuser', $feuser);
		}
		else {
			$viewData->offsetSet('part', 'CONFIRMFAILED');
			$viewData->offsetSet('feuser', '');
		}
	}
	
  function getTemplateName() { return 'registrationConfirm';}
	function getViewClassName() { return 'tx_t3users_views_ShowRegistrationConfirm';}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_RequestConfirmEmail.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_RequestConfirmEmail.php']);
}


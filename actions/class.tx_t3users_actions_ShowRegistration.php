<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche (rene@system25.de)
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
require_once(t3lib_extMgm::extPath('rn_base') . 'util/class.tx_rnbase_util_DB.php');
if(t3lib_extMgm::isLoaded('ameos_formidable')) {
	require_once(t3lib_extMgm::extPath('ameos_formidable') . 'api/class.tx_ameosformidable.php');
}
tx_rnbase::load('tx_rnbase_action_BaseIOC');
tx_rnbase::load('tx_t3users_models_feuser');


/**
 * Controller für die Neuregistrierung
 *
 */
class tx_t3users_actions_ShowRegistration extends tx_rnbase_action_BaseIOC {
	private $feuser;
	private $afterRegistrationPID;
	private $userDataSaved = false;

	function handleRequest(&$parameters,&$configurations, &$viewData){
		global $TSFE;
		$this->conf = $configurations;
		$hideForm = false;
		$viewData->offsetSet('part', 'REGISTER');
		$confirm = $parameters->offsetGet('NK_confirm');
		if($confirm) {
			$hideForm = true;
			// User wants to be confirmed
			$userUid = intval($parameters->offsetGet('NK_uid'));
			// Load instance
			$feuser = tx_t3users_models_feuser::getInstance($userUid);
			$usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
			// Set config
			$options['successgroupsadd'] = $configurations->get('userGroupAfterConfirmation');
			$options['successgroupsremove'] = $configurations->get('userGroupUponRegistration');
			$confirmed = $usrSrv->confirmUser($feuser, $confirm, $options);
			if($confirmed) {
				$viewData->offsetSet('part', 'CONFIRMED');
				$viewData->offsetSet('confirmed', $feuser);
			}
			else {
				$viewData->offsetSet('part', 'CONFIRMFAILED');
				$viewData->offsetSet('confirmed', '0');
			}
		}
		elseif($parameters->offsetGet('NK_saved')) {
			$viewData->offsetSet('part', 'REGISTERFINISHED');
			$hideForm = true;
		}
		$editors = $this->getEditors($parameters, $configurations, $hideForm);
		//elseif($parameters->offsetGet('NK_saved')) {
		if($this->userDataSaved) {
			// Redirect nach dem Versand der Email
	    $link = $configurations->createLink();
	    $link->destination($GLOBALS['TSFE']->id); // Link auf aktuelle Seite
	    // Zusätzlich Parameter für Finished setzen
	    $link->parameters(array('NK_saved' => '1', 'NK_reguser' => $uid));
	    $redirect_url = $link->makeUrl(false);
	    header('Location: '.t3lib_div::locationHeaderUrl($redirect_url));
		}
	// index.php?id=38&amp;rnuser%5BNK_confirm%5D=5d52036ce724a231ab8d90ab120638db&amp;rnuser%5BNK_uid%5D=4&amp;cHash=c19b590e9c


		$viewData->offsetSet('editors', $editors );
	}
	

	private function getEditors($parameters, $configurations, $hide) {
		$editors['FORM'] = '';
		if($hide) return $editors;
		$ameosClass = $configurations->get('showregistration.ameos');
		if($ameosClass) {
			$this->regForm =& tx_rnbase::makeInstance($ameosClass);
			$this->regForm->setConfigurations($configurations, 'showregistration.');
		}
		else {
			$this->regForm =& t3lib_div::makeInstance('tx_ameosformidable');
		}

		$xmlfile = $configurations->get('showregistration.formxml');
		$xmlfile = $xmlfile ? $xmlfile : t3lib_extmgm::extPath('t3users') . '/forms/registration.xml';
		$this->regForm->init($this,$xmlfile,false);
		$editors['FORM'] = $this->regForm->render();
		
		

		return $editors;
	}

	/**
	 * Set PID
	 *
	 * @param array $params
	 * @param tx_ameosformidable $form
	 */
	public function handleBeforeUpdateDB($params, $form) {
		$params['confirmstring'] = md5(uniqid());
		$pid = t3lib_div::intExplode(',',$this->conf->get('feuserPages'));
		$params['pid'] = (is_array($pid) && count($pid)) ? $pid[0] : 0;
		$params['disable'] = 1;
		$params['tstamp'] = time();
		$params['crdate'] = $params['tstamp'];
		$groupId = intval($this->conf->get('userGroupUponRegistration'));
		$params['usergroup'] = $groupId;
		$params['name'] = trim($params['first_name'] . ' ' .$params['last_name']);
		$usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
		if($usrSrv->useMD5())
			$params['password'] = md5($params['password']);
		
		tx_rnbase_util_Misc::callHook(
			't3users',
			'showRegistration_beforeUpdateDB_hook',
			array(
				'params' => &$params,
				'form' => &$form
			),
			$this
		);
		return $params;
	}
	/**
	 * User is saved. Send confirmation mail
	 *
	 * @param array $params
	 * @param tx_ameosformidable $form
	 */
	public function handleUpdateDB($params, $form) {
				
		$uid = $form->oDataHandler->newEntryId;
		
		tx_rnbase_util_Misc::callHook(
			't3users',
			'showRegistration_beforeSendConfirmationMail_hook',
			array(
				'params' => &$params,
				'form' => &$form,
				'newEntryId' => $uid
			),
			$this
		);
		
		// Mail schicken
		$token = md5(microtime());
		$link = $this->conf->createLink();
		$link->label($token);
		$confirmPage = intval($this->conf->get('showregistration.links.mailconfirm.pid'));
		$link->destination($confirmPage ? $confirmPage : $GLOBALS['TSFE']->id);
		// Zusätzlich Parameter für Finished setzen
		$link->parameters(array('NK_confirm' => $params['confirmstring'],
														'NK_uid' => $uid));


		$linkMarker = 'MAILCONFIRM_LINK';
		$wrappedSubpartArray['###'.$linkMarker . '###'] = explode($token, $link->makeTag());
		$markerArray = $params;
		$markerArray['###'.$linkMarker . 'URL###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $link->makeUrl(false);
		$markerArray['###SITENAME###'] = $this->conf->get('siteName');
		$subpartArray = array();

		$template = $this->conf->getLL('registration_confirmation_mail');
		$mailtext = $this->conf->getFormatter()->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

		$markerArray = array();
		tx_rnbase::load('tx_rnbase_util_BaseMarker');
		tx_rnbase_util_BaseMarker::callModules($mailtext, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $this->conf->getFormatter());
		$mailtext = $this->conf->getFormatter()->cObj->substituteMarkerArrayCached($mailtext, $markerArray, $subpartArray, $wrappedSubpartArray);
		// Now send mail
		$userEmail = $params['email'];
		$from = $this->conf->get('showregistration.email.from');
		$fromName = $this->conf->get('showregistration.email.fromName');
		$cc = $this->conf->get('showregistration.email.cc');
		$this->conf->getFormatter()->cObj->sendNotifyEmail($mailtext, $userEmail, $cc, $from, $fromName, $userEmail);
		$this->userDataSaved = true;
	}
	public function nextPage($params) {
		$this->regValues = $params;
	}

	// TODO: remove method
	function getValue($param) {
		if($param['col'] == 'username') {
			return 'Testuser';
		}
		return $this->regValues[$param['col']];
	}

  /**
   * The HTML-Template for registration form
   *
   * @return string path name
   */
  function getFormTemplatePath() {
  	$path = t3lib_div::getFileAbsFileName($this->conf->get('showregistration.form'));
  	return $path;
  }
  
  function getTemplateName() { return 'registration';}
	function getViewClassName() { return 'tx_t3users_views_ShowRegistration';}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_ShowRegistration.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_ShowRegistration.php']);
}

?>
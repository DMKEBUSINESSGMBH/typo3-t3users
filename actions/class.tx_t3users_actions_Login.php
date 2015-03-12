<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche (dev@dmk-ebusiness.de)
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
 * Controller fuer Loginbox
 *
 */
class tx_t3users_actions_Login extends tx_rnbase_action_BaseIOC {

	/**
	 * UserCases:
	 * 1. ForgotPassword
	 * 2. Show Login-Box (if not logged in)
	 * 3. Show Welcome Message (if logged in right now)
	 * 4. Show Status (if logged in)
	 *
	 * @param array_object $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewData
	 * @return string error msg or null
	 */
	function handleRequest(&$parameters,&$configurations, &$viewData){
		// Find action: login, logout, forgotPassword
		$action = t3lib_div::_GP('logintype');
		$finished = intval($parameters->offsetGet('NK_loginfinished'));
		if($finished) $action = 'login';

		$loginActionOnly = $configurations->get($this->getConfId().'loginActionOnly');
		$loginActionOnly = $loginActionOnly && (strtolower($loginActionOnly) == 'true' || intval($loginActionOnly) > 0);
		if(!$action && !$loginActionOnly) {
			// no action found. Check forgot password
			if(intval($parameters->offsetGet('NK_forgotpass')))
				$action = 'forgotpass';
			// no action found. Check request confirmation mail
			if(intval($parameters->offsetGet('NK_requestconfirmation')))
				$action = 'requestconfirmation';
		}

		$feuser = tx_t3users_models_feuser::getCurrent();
		if(is_object($feuser))
			$viewData->offsetSet('feuser', $feuser);
		// User is logged in
		if($action == 'login' && is_object($feuser)) {
			// The user logged in right now, so show the welcome stuff
			$this->handleLoginConfirmed($action, $parameters,$configurations, $viewData, $feuser);
		}
		elseif($action == 'forgotpass') {
			$this->handleForgotPass($parameters,$configurations, $viewData);
		}
		elseif($action == 'requestconfirmation') {
			$this->handleRequestConfirmation($parameters,$configurations, $viewData);
		}
		elseif(is_object($feuser)) {
			// The user is logged in, so show the status and logout stuff
			$this->handleLoggedin($action, $parameters,$configurations, $viewData, $feuser);
		}
		else {
			// User is not logged in, so show login box
			$this->handleNotLoggedIn($action, $parameters,$configurations, $viewData);
		}
		// Ueber die viewdata koennen wir Daten in den View transferieren
		$viewData->offsetSet('data', 'test');

    return null;
  }

	/**
	 * Send confirmation mail to user
	 *
	 * @param array_object $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewData
	 */
	private function handleRequestConfirmation(&$parameters,&$configurations, &$viewData){
		$viewData->offsetSet('subpart', '###TEMPLATE_REQUESTCONFIRMATION###');
		$this->setLanguageMarkers($markerArr, $configurations, 'requestconfirmation');
		$markerArr['action_uri'] = $this->createPageUri($configurations, array('NK_requestconfirmation' => '1'), true);
		// Is nutzer in request und zwischen im zustand registrierung und voll qualifiziertem login?
		// Stati:
		// 1. alles klappt: Meldung FE und Infomail
		// 2. Nutzer nicht gefunden: Meldung im FE
		// 3. Sonstiger Fehler: Meldung im FE

		$email = $parameters->offsetGet('NK_requestconfirmation_email');
		if ($email && t3lib_div::validEmail($email) ) {
			$usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();

			$markerArr['your_email'] = $email;
			$storagePid = $this->getStoragePid($configurations);
			$feuser = $usrSrv->getDisabledUserByEmail($email, $storagePid);
			if($feuser) {
				$viewData->offsetSet('subpart', '###TEMPLATE_REQUESTCONFIRMATION_SENT###');
				$this->setLanguageMarkers($markerArr, $configurations, 'requestconfirmation_sent');
				// TODO: Mailversand in eigene Methode verlegen
				// TODO: Direkt auf Service mit niedriger Prio umstellen. Das TS muss wieder raus!
				// an external service should be able to handle this case
				$regSrv = tx_t3users_util_ServiceRegistry::getRegistrationService();
				$regSrv->handleRequestConfirmation($feuser, $configurations, 'loginbox.');
			}
			else {
				$this->setLanguageMarkers($markerArr, $configurations, 'requestconfirmation_notfound');
			}
		}
		else
			$this->setLanguageMarkers($markerArr, $configurations, 'requestconfirmation');

		$viewData->offsetSet('markers', $markerArr);
	}

	/**
	 * Send new password to user
	 *
	 * @param array_object $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewData
	 */
	function handleForgotPass(&$parameters,&$configurations, &$viewData){
		$viewData->offsetSet('subpart', '###TEMPLATE_FORGOT###');
		$this->setLanguageMarkers($markerArr, $configurations, 'forgot');
		$markerArr['action_uri'] = $this->createPageUri($configurations, array('NK_forgotpass' => '1'), true);
		// Is email in request?
		// Stati:
		// 1. alles klappt: Meldung FE und Infomail
		// 2. Nutzer nicht gefunden: Meldung im FE
		// 3. Sonstiger Fehler: Meldung im FE
		$email = $parameters->offsetGet('NK_forgot_email');
		if ($email && t3lib_div::validEmail($email) ) {
			$markerArr['your_email'] = $email;
			$usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
			$storagePid = $this->getStoragePid($configurations);
			$feuser = $usrSrv->getUserByEmail($email, $storagePid);
			if($feuser) {
				$viewData->offsetSet('subpart', '###TEMPLATE_FORGOT_SENT###');
				$this->setLanguageMarkers($markerArr, $configurations, 'forgot_sent');

				// TODO: Direkt auf Service mit niedriger Prio umstellen.
				// an external service should be able to handle this case
				$regSrv = tx_t3users_util_ServiceRegistry::getRegistrationService();
				$regSrv->handleForgotPass($feuser, $configurations , 'loginbox.');
			}
			else {
				$this->setLanguageMarkers($markerArr, $configurations, 'forgot_notfound');
			}
		}
		else
			$this->setLanguageMarkers($markerArr, $configurations, 'forgot');

		$viewData->offsetSet('markers', $markerArr);
	}
  /**
	 * User is not logged in
	 *
	 * @param string $action
	 * @param array_object $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewData
	 */
	function handleNotLoggedIn($action, &$parameters,&$configurations, &$viewData){
		$viewData->offsetSet('subpart', '###TEMPLATE_LOGIN###');

		if($action == 'login') {
			// This was a failed login attempt
			// Maybe the user tried login with password
//			$username = t3lib_div::_GP('user');
			// Annahme: Username fuer Passwort herausfinden und redirect mit Superchallenge
			// Voraussetzung: 2. challengewert mit email
//t3lib_div::debug($username, 'tx_t3users_actions_Login'); // TODO: Remove me!
			$statusKey = 'login_error';
		}
		elseif($action == 'logout') {
			// User logged out
			$statusKey = 'goodbye';
		}
		else {
			$statusKey = 'logout';
			if($markerArr['redirect_url'] == '' && $configurations->get($this->getConfId().'redirectMode') == 'referrer')
				$markerArr['redirect_url'] = htmlspecialchars(t3lib_div::getIndpEnv('HTTP_REFERER'));
			if($markerArr['redirect_url'] == '' && $configurations->get($this->getConfId().'redirectMode') == 'force')
				$markerArr['redirect_url'] = htmlspecialchars(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'));
		}

		// Wenn explizit eine URL mitgegeben wurde, nutzen wir diese!
		if(strlen($redirectUrl = t3lib_div::_GP('redirect_url')) && t3lib_div::isOnCurrentHost($redirectUrl)){
			$markerArr['redirect_url'] = $redirectUrl;
		}

		$markerArr['redirect_url'] = preg_replace("/[&?]logintype=[a-z]+/", '', $markerArr['redirect_url']);

		$this->setLanguageMarkers($markerArr, $configurations, $statusKey);
		$markerArr['storage_pid'] = $this->getStoragePid($configurations);
//		$markerArr['status_message'] = $configurations->getCfgOrLL('loginbox.msg.login_error');

		$markerArr['action_uri'] = $this->createPageUri($configurations);
		// Prepare some stuff for login
		$this->prepareLoginFormOnSubmit($markerArr, $statusKey, $configurations, $this->getConfId());
		$viewData->offsetSet('markers', $markerArr);
	}

	/**
	 * User is logged in. Show Status and logout-Button
	 *
	 * @param string $action
	 * @param array_object $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewData
	 * @param tx_t3users_models_feuser $feuser
	 */
	function handleLoggedin($action, &$parameters,&$configurations, &$viewData, &$feuser){
		$viewData->offsetSet('subpart', '###TEMPLATE_STATUS###');
		$this->setLanguageMarkers($markerArr, $configurations, 'login');
		$markerArr['storage_pid'] = $this->getStoragePid($configurations);
		$params = array();
		if($parameters->offsetGet('NK_logintype')) { // User want's to logout
			if(tx_rnbase_configurations::getExtensionCfgValue('t3users', 'trackLogin')) {
				tx_t3users_util_ServiceRegistry::getLoggingService()->logLogout($feuser->uid);
			}
			// Redirect with logout
			$redirect = intval($configurations->get('loginbox.logoutRedirectPage'));
			$link = $configurations->createLink();
			// Initialisieren und zusaetzlich Parameter fuer Finished setzen
			$link->initByTS($configurations, $this->getConfId().'links.logoutRedirect.', array('logintype' => 'logout'));
			$link->designatorString = '';

			//soll das Formular auf eine bestimmte Seite abgeschickt werden?
			if ($redirect) {
				$link->destination($redirect);
			}
			// wir brauchen eine absolute url für den redirect
			if (!$link->isAbsUrl()) {
				$link->setAbsUrl(true);
			}

			// redirect durchführen
			$link->redirect();
		}
		// Direkt weiterleiten, wenn redirect_url angegeben
		// wird bei externen Links, z.B. Newsletter genutzt, die auf geschützte Bereiche verweisen
		// ist der User bereits eingeloggt, dann tritt dieser Fall in Kraft.
		elseif(strlen($redirectUrl = t3lib_div::_GP('redirect_url')) && t3lib_div::isOnCurrentHost($redirectUrl)){
			header('Location: '.t3lib_div::locationHeaderUrl($redirectUrl));
		}
		$markerArr['action_uri'] = $this->createPageUri($configurations);

		$viewData->offsetSet('markers', $markerArr);
	}
	/**
	 * User logged in right now. Show Welcome Message
	 *
	 * @param string $action
	 * @param array_object $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewData
	 * @param tx_t3users_models_feuser $feuser
	 */
	function handleLoginConfirmed($action, &$parameters,&$configurations, &$viewData, &$feuser){
		$finished = intval($parameters->offsetGet('NK_loginfinished'));

		tx_rnbase_util_Misc::callHook(
			't3users','beforeLoginConfirmed',
			array(
				'action' 			=> $action,
				'parameters' 		=> $parameters,
				'configurations'	=> $configurations,
				'viewData' 			=> $viewData,
				'feuser' 			=> $feuser,
				'finished' => $finished
			),
			$this
		);

		if(!$finished) {
			if(tx_rnbase_configurations::getExtensionCfgValue('t3users', 'trackLogin')) {
				tx_t3users_util_ServiceRegistry::getLoggingService()->logLogin($feuser->uid);
			}
			// Redirect to same page to avoid forced logout
			// Alternativ we redirect to a configured page
			$redirect = $configurations->get('loginbox.loginRedirectPage');
			$redirectMode = $configurations->get($this->getConfId().'redirectMode');
			if($configurations->get($this->getConfId().'redirectMode') == 'forceRequestUrl')
				$redirect = htmlspecialchars(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'));
			// Wenn explizit eine URL mitgegeben wurde, nutzen wir diese!
			elseif(strlen($redirectUrl = t3lib_div::_GP('redirect_url')) && t3lib_div::isOnCurrentHost($redirectUrl)){
				$redirect = $redirectUrl;
			}

			$link = $configurations->createLink();
			// Initialisieren und zusaetzlich Parameter fuer Finished setzen
			$link->initByTS($configurations, $this->getConfId().'links.loginRedirect.', array('NK_loginfinished' => '1'));
			//soll das Formular auf eine bestimmte Seite abgeschickt werden?
			if ($redirect) {
				$link->destination($redirect);
			}
			// wir brauchen eine absolute url für den redirect
			if (!$link->isAbsUrl()) {
				$link->setAbsUrl(true);
			}
			$link->redirect();
		}
		$viewData->offsetSet('subpart', '###TEMPLATE_WELCOME###');
		$this->setLanguageMarkers($markerArr, $configurations, 'welcome');

		$viewData->offsetSet('markers', $markerArr);
	}

	/**
	 * Add some common markers
	 *
	 * @param array $markerArr
	 * @param tx_rnbase_configurations $configurations
	 * @param string $statusKey
	 */
	function setLanguageMarkers(&$markerArr, &$configurations, $statusKey) {
		$labels = array('username', 'password', 'login', 'logout', 'permalogin', 'forgot_password',
						'email', 'sendpass', 'register');
		foreach($labels As $label) {
			$markerArr['label_'.$label] = $configurations->getLL('label_' . $label);
		}
		$markerArr['status_header'] = $configurations->getCfgOrLL('loginbox.header_'.$statusKey);
		$markerArr['status_message'] = $configurations->getCfgOrLL('loginbox.message_'.$statusKey);

		$markerArr['prefixid'] = $configurations->getQualifier();
		$storagePid = $this->getStoragePid($configurations);
		$markerArr['storage_pid'] = $storagePid;
		$usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
		$markerArr['user_online'] = $usrSrv->getOnlineUsers($storagePid);
		// Hook to append other markers
		tx_rnbase_util_Misc::callHook('t3users','loginboxmarker',
			array('markerArr' => &$markerArr, 'conf' => $configurations, 'status' => $statusKey), $this);
	}
	function getStoragePid(&$configurations) {
		return $configurations->get('feuserPages');
	}
	/**
	 *
	 * Erstellt die URL für das Formular
	 * @param tx_rnbase_configurations $configurations
	 * @param array $params
	 * @param boolean $nocache
	 */
	function createPageUri(&$configurations, $params = array(), $nocache = false) {
		$redirectMode = $configurations->get($this->getConfId().'redirectMode');
		if($redirectMode == 'force' || $redirectMode == 'forceRequestUrl') {
			// Redirect auf aktuelle Seite
			return t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
		}
		$link = $configurations->createLink();
		$link->initByTS($configurations, $this->getConfId().'actionUrl.', $params);
		//soll das Formular auf eine bestimmte Seite abgeschickt werden?
		// die TargetPid wird weiter unterstützt
		$targetPid = $configurations->get($this->getConfId().'targetPid');
		if($targetPid)
			$link->destination($targetPid);
		// No Cache wird weiter unterstützt, ist aber eigentlich nicht notwendig.
		if($nocache)
			$link->nocache();
		return $link->makeUrl(false);
	}
	/**
	 * Prepares TYPO3 to login with superchallenged password. This method is taken and modified
	 * from kbmd5fepw.
	 *
	 * @return string hidden field with challenge value
	 */
	function prepareLoginFormOnSubmit(&$markerArr, $statusKey, $configurations, $confId) {
		$code = new stdClass();
		$code->onsubmit ='';
		$code->formFields ='';
		$code->jsCode ='';
		$code->jsFiles ='';

		$srv = tx_t3users_util_ServiceRegistry::getLoginFormService();
		$srv->extendLoginForm($code, $statusKey, $configurations, $confId, $this);

		$markerArr['extra_hidden'] = '';

		// Daten integrieren
		if($code->onsubmit)
			$markerArr['on_submit'] = $code->onsubmit;
		if($code->formFields)
			$markerArr['extra_hidden'] = $code->formFields;

		if($code->jsFiles)
			$GLOBALS['TSFE']->additionalHeaderData['tx_t3users'] .= $code->jsFiles;

		if($code->jsCode)
			$GLOBALS['TSFE']->JSCode .= $code->jsCode;
	}
	function getConfId() { return 'loginbox.';}
	function getTemplateName() { return 'login';}
	function getViewClassName() { return 'tx_t3users_views_Login';}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_Login.php'])	{
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_Login.php']);
}

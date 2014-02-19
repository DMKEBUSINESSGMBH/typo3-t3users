<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2010 Rene Nitzsche (nitzsche@das-medienkombinat.de)
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
 * Controller for edit form of FE-User
 *
 */
class tx_t3users_actions_EditFeUser extends tx_rnbase_action_BaseIOC {
  
	/**
	 * Erstmal nur das eigene Profil bearbeiten
	 *
	 * @param tx_rnbase_IParameter $parameters
	 * @param tx_rnbase_configurations $configurations
 	 * @param array $viewData
	 * @return string error msg or null
	 */
	function handleRequest(&$parameters,&$configurations, &$viewData){
		$this->conf =& $configurations;
		//Bei Modus "check" werden die Daten aus der Url in die DB geschrieben (wenn
		//möglich). Ansonsten wird ganz normal die Form geparsed.
		if($this->conf->get($this->getConfId().'mode') == 'check'){
			//uid und confirmstring sind nicht in tca weshalb wir sie
			//fest auslesen müssen
			$uid = $parameters->offsetGet('NK_uid');
			$confirmstring = $parameters->offsetGet('NK_confirmstring');
			
			if(empty($uid) || empty($confirmstring))
				return $configurations->getLL('msg_change_error');
				
			//leeres Model um alle DB Felder auszulesen
			$feUser = tx_rnbase::makeInstance('tx_t3users_models_feuser', array('uid'=>0));
			
			//für jedes Feld in der DB prüfen ob ein Wert übermittelt wurde
			foreach($feUser->getColumnNames() as $cols)
				if($parameters->offsetExists('NK_'.$cols))
					$params[$cols] = $parameters->offsetGet('NK_'.$cols);
					
			//zur Sicherheit email == username setzen
			if(!empty($params['email']))
				$params['username'] = $params['email'];
			//confirmstring wieder auf '' setzen
			$params['confirmstring'] = '';
			//und ab damit
			$feUserSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
			if($feUserSrv->updateFeUserByConfirmstring($uid,$confirmstring,$params))
				return $configurations->getLL('msg_change_success');
			else
				return $configurations->getLL('msg_change_error');
		}else{
			$feuser = tx_t3users_models_feuser::getCurrent();
			if(!$feuser)
				return $configurations->getLL('notLoggedIn');
	
			$form = $this->getEditors($parameters, $configurations, $feuser);
			$viewData->offsetSet('form', $form->render());
			$viewData->offsetSet('user', $feuser);
		}
	}

	/**
	 * Liefert den Editor
	 *
	 * @param array $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param tx_a4base_models_organisation $org
	 * @return tx_a4base_util_Formidable
	 */
	private function getEditors($parameters, $configurations, $item) {
		if(!t3lib_extMgm::isLoaded('mkforms')) {
			$this->markTestSkipped('mkforms ist nicht installiert.');
		}
			
		tx_rnbase::load('tx_mkforms_forms_Factory');
		$this->form = tx_mkforms_forms_Factory::createForm('');
		$formXml = $configurations->get($this->getConfId().'formxml');
		
    	$this->editItem = $item;
    	$itemUid = ($this->editItem) ? $this->editItem->getUid() : 0;

    	$this->form->init($this, $formXml, $itemUid/*, $this->config*/);
    	
    	return $this->form;

//der alte Weg
//		$ret = array();
//
//		$this->editItem = $item;
//		$this->editForm =& tx_rnbase::makeInstance('tx_mkameos_util_Ameos');
//		$this->editForm->setConfigurations($configurations, $this->getConfId());
//		$formXml = $configurations->get($this->getConfId().'form');
//		$formXml = $formXml ? $formXml : t3lib_extmgm::extPath('t3users') . 'forms/feuser_edit.xml';
//		$itemUid = ($this->editItem) ? $this->editItem->getUid() : 0;
//		$this->editForm->setStore('itemid', $itemUid);
//		$this->editForm->setStore('itemclazz', 'tx_t3users_models_feuser');
//		$this->editForm->init($this,$formXml, $itemUid);
//		return $this->editForm;
	}
	/**
	 * Modify user before update to db
	 *
	 * @param array $params
	 * @param tx_mkameos_util_Ameos $form
	 */
	public function handleBeforeUpdateDB($params, $form) {
		$feUser = tx_t3users_models_feuser::getCurrent();
		//If enableNonTcaColumns is set: do not eliminate the NonTCA-Enabled columns
		if(! $this->conf->get($this->getConfId().'enableNonTcaColumns')){
			//leeres Model bilden um Felder zu löschen die da nicht hingehören
			tx_rnbase::load('tx_mklib_util_TCA');
			$params = tx_mklib_util_TCA::eleminateNonTcaColumns($feUser,$params);

		}
		//wenn die Option doubleoptin gewählt wurde dann werden die daten noch nicht
		//gespeichert sondern mit einem confirmstring per email verschickt und
		//erst bei der Bestätigung in die DB geschrieben
		if($this->conf->get($this->getConfId().'doubleoptin')){
			//Zusätzlich Parameter setzen
			//Bestätigungscode generieren
			$params['confirmstring'] = md5(uniqid());
			$params['uid'] = $feUser->getUid();
			//username == mail setzen
			$params['username'] = $params['email'];
			//Mail schicken
			$emailService = tx_t3users_util_ServiceRegistry::getEmailService();
			$emailService->sendEditedData($feUser, $params, $this->conf , $this->getConfId());
			//alles außer confirmstring löschen damit nur dieser in die db wandert
			$confirmString = $params['confirmstring'];
			unset($params);
			$params['confirmstring'] = $confirmString;
		}else{
			$params['tstamp'] = time();
			$params['name'] = trim($params['first_name'] . ' ' .$params['last_name']);
			if($params['password123']) {
				$params['password'] = $params['password123'];
				$usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
				if($usrSrv->useMD5())
					$params['password'] = md5($params['password123']);
			}
		}
		
		return $params;
	}
	/**
	 * User is saved.
	 *
	 * @param array $params
	 * @param tx_ameosformidable $form
	 */
	public function handleUpdateDB($params, $form) {
		// Wohin soll umgeleitet werden?
		$redirect = intval($this->conf->get($this->getConfId().'redirect.pid'));
			
		$link = $this->conf->createLink();
		$link->destination($redirect ? $redirect : $GLOBALS['TSFE']->id);//fallback
		$redirect_url = $link->makeUrl(false);
		header('Location: '.t3lib_div::locationHeaderUrl($redirect_url));
	}
	
	function getTemplateName() { return 'feuseredit';}
	function getViewClassName() { return 'tx_t3users_views_EditFeUser';}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_EditFeUser.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_EditFeUser.php']);
}

?>
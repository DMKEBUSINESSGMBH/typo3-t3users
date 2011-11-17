<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Holger Gebhardt <gebhardt@das-medienkombinat.de>
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
require_once(PATH_t3lib.'class.t3lib_svbase.php');
tx_rnbase::load('tx_rnbase_util_Templates');

/**
 * Service to send emails
 * 
 */
class tx_t3users_services_email extends t3lib_svbase {
	/**
	 * Sends newPassword to the feUser
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param string $newPassword
	 * @param tx_rnbase_configurations $configurations
	 */
	public function sendNewPassword($feuser, $newPassword, $configurations, $confId = 'loginbox.') {
		if(t3lib_extMgm::isLoaded('mkmailer')) {
			return $this->sendNewPasswordMkMailer($feuser, $newPassword, $configurations, $confId);
		}
		return $this->sendNewPasswordSimple($feuser, $newPassword, $configurations, $confId);
	}
	/**
	 * Sends newPassword to the feUser
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param string $newPassword
	 * @param tx_rnbase_configurations $configurations
	 */
	private function sendNewPasswordSimple($feuser, $newPassword, $configurations, $confId) {
		// Mail vorbereiten
		$template = $configurations->getLL('loginbox_forgot_infomail');
		$mailMarker['###PASSWORD###'] = $newPassword;
		$formatter = $configurations->getFormatter();
		$mailtext = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $mailMarker);
		
		// Jetzt noch den FeuserMarker
		$marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');
		$mailtext = $marker->parseTemplate($mailtext, $feuser, $formatter, $confId.'feuser.');
		$emailFrom = $configurations->get($confId.'emailFrom');
		$emailFromName = $configurations->get($confId.'emailFromName');
		$emailReply = $configurations->get($confId.'emailReply');

		$configurations->getCObj()->sendNotifyEmail($mailtext, $feuser->getEmail(), '', $emailFrom, $emailFromName, $emailReply);
	}
	
	
	/**
	 * Sends newPassword to the feUser
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param string $newPassword
	 * @param tx_rnbase_configurations $configurations
	 */
	private function sendNewPasswordMkMailer($feuser, $newPassword, $configurations, $confId) {
		// TODO: Make alternative mailtemplate via locallang.xml possible, use the lines below
		#$emailFrom = $this->configurations->get('loginbox.emailFrom');
		#$emailFromName = $this->configurations->get('loginbox.emailFromName');
		#$emailReply = $this->configurations->get('loginbox.emailReply');

		tx_rnbase::load('tx_mkmailer_util_ServiceRegistry');
		$mailSrv = tx_mkmailer_util_ServiceRegistry::getMailService();
		$templateObj = $mailSrv->getTemplate('t3users_sendnewpassword');

		$markerArray = array();
		$markerArray['###PASSWORD###'] = $newPassword;

		tx_rnbase::load('tx_rnbase_util_Templates');
		$messageTxt = tx_rnbase_util_Templates::substituteMarkerArrayCached($templateObj->getContentText(), $markerArray, $subpartArray, $wrappedSubpartArray);
		$messageHtml = tx_rnbase_util_Templates::substituteMarkerArrayCached($templateObj->getContentHtml(), $markerArray, $subpartArray, $wrappedSubpartArray);

		// feUser-Marker werden mittels Marker-Klasse ersetzt
		$formatter = $configurations->getFormatter();
		$marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');
		$messageTxt = $marker->parseTemplate($messageTxt, $feuser, $formatter, $confId.'feuser.');
		$messageHtml = $marker->parseTemplate($messageHtml, $feuser, $formatter, $confId.'feuser.');

		$receiver = tx_rnbase::makeInstance('tx_mkmailer_receiver_FeUser');
		$receiver->setFeUser($feuser);

		$job = tx_rnbase::makeInstance('tx_mkmailer_mail_MailJob');
		$job->addReceiver($receiver);
		$job->setFrom($templateObj->getFromAddress());
		$job->setCCs($templateObj->getCcAddress());
		$job->setBCCs($templateObj->getBccAddress());
		$job->setSubject($templateObj->getSubject());
		$job->setContentText($messageTxt);
		$job->setContentHtml($messageHtml);
		$mailSrv->spoolMailJob($job);
	}
	
	/**
	 * Send edited feUser data to his email for confirmation
	 *
	 * @param tx_t3users_models_feuser $feUser
	 * @param array $data
	 * @param tx_rnbase_configurations $configurations
	 */
	public function sendEditedData($feUser, $data, $configurations, $confId = 'feuseredit.') {
		tx_rnbase::load('tx_mkmailer_util_ServiceRegistry');
		$mailSrv = tx_mkmailer_util_ServiceRegistry::getMailService();
		$templateObj = $mailSrv->getTemplate('t3users_confirmdatachange');
		
		//Link zur Bestätigungsseite erstellen
		$token = md5(microtime());
		$link = $configurations->createLink();
		$link->label($token);
		$doubleOptInPage = intval($configurations->get($confId.'doubleoptin.pid'));
		$link->destination($doubleOptInPage ? $doubleOptInPage : $GLOBALS['TSFE']->id);
		$link->setAbsUrl(true);

		$markerArray = array();
		//Daten extrahieren
		//Daten, die geändert werden sollen (also mit Link mitgeschickt werden)
		foreach($data as $key => $value){
			$markerArray['###'.strtoupper($key).'###'] = $value;
			$linkParams['NK_'.$key] = $value;	
		}
		$link->parameters($linkParams);

		$linkMarker = 'MAILCONFIRM_LINK';
		$wrappedSubpartArray['###'.$linkMarker . '###'] = explode($token, $link->makeTag());

		$markerArray = $data;
		$markerArray['###'.$linkMarker . 'URL###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $link->makeUrl(false);
			
		tx_rnbase::load('tx_rnbase_util_Templates');
		$messageTxt = tx_rnbase_util_Templates::substituteMarkerArrayCached($templateObj->getContentText(), $markerArray, $subpartArray, $wrappedSubpartArray);
		$messageHtml = tx_rnbase_util_Templates::substituteMarkerArrayCached($templateObj->getContentHtml(), $markerArray, $subpartArray, $wrappedSubpartArray);

		// feUser-Marker werden mittels Marker-Klasse ersetzt
		$formatter = $configurations->getFormatter();
		$marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');
		$messageTxt = $marker->parseTemplate($messageTxt, $feUser, $formatter, $confId.'feuser.');
		$messageHtml = $marker->parseTemplate($messageHtml, $feUser, $formatter, $confId.'feuser.');

		//haben sich die Daten geändert dann nehmen wir einen anderen receiver
		//um den nochmaligen versand an die neue adresse zu verhindern
		if($feUser->record['email'] != $data['email'])
			$receiver = tx_rnbase::makeInstance('tx_t3users_receiver_FeUserChanged');
		else
			$receiver = tx_rnbase::makeInstance('tx_mkmailer_receiver_FeUser'); 
		$receiver->setFeUser($feUser);

		$job = tx_rnbase::makeInstance('tx_mkmailer_mail_MailJob');
		$job->addReceiver($receiver);
		$job->setFrom($templateObj->getFromAddress());
		$job->setCCs($templateObj->getCcAddress());
		$job->setBCCs($templateObj->getBccAddress());
		$job->setSubject($templateObj->getSubject());
		$job->setContentText($messageTxt);
		$job->setContentHtml($messageHtml);
		$mailSrv->spoolMailJob($job);
	}

	/**
	 * Sends confirmLink to the feUser
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param tx_rnbase_util_Link $confirmLink
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 */
	public function sendConfirmLink($feuser, $confirmLink, $configurations, $confId = 'loginbox.') {
		if(t3lib_extMgm::isLoaded('mkmailer')) {
			return $this->sendConfirmLinkMkMailer($feuser, $confirmLink, $configurations, $confId);
		}
		return $this->sendConfirmLinkSimple($feuser, $confirmLink, $configurations, $confId);
	}
	/**
	 * Sends newPassword to the feUser
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param tx_rnbase_util_Link $confirmLink
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 */
	private function sendConfirmLinkSimple($feuser, $confirmLink, $configurations, $confId) {
		// Mail vorbereiten
		$template = $configurations->getLL('loginbox_requestconfirmlink_infomail');
				
		$mailMarker['###CONFIRMLINKURL###'] = $confirmLink->makeUrl();
		$mailWrappedSubpart['###CONFIRMLINK###'] = explode($confirmLink->getLabel(), $confirmLink->makeTag());
		$formatter = $configurations->getFormatter();
		$mailtext = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $mailMarker, array(), $mailWrappedSubpart);
		
		// Jetzt noch den FeuserMarker
		$marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');
		$mailtext = $marker->parseTemplate($mailtext, $feuser, $formatter, $confId.'feuser.');
		$emailFrom = $configurations->get($confId.'emailFrom');
		$emailFromName = $configurations->get($confId.'emailFromName');
		$emailReply = $configurations->get($confId.'emailReply');

		$configurations->getCObj()->sendNotifyEmail($mailtext, $feuser->getEmail(), '', $emailFrom, $emailFromName, $emailReply);
	}
	
	
	/**
	 * Sends newPassword to the feUser
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param tx_rnbase_util_Link $confirmLink
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 */
	private function sendConfirmLinkMkMailer($feuser, $confirmLink, $configurations, $confId) {
		// TODO: Make alternative mailtemplate via locallang.xml possible, use the lines below
		#$emailFrom = $this->configurations->get('loginbox.emailFrom');
		#$emailFromName = $this->configurations->get('loginbox.emailFromName');
		#$emailReply = $this->configurations->get('loginbox.emailReply');

		tx_rnbase::load('tx_mkmailer_util_ServiceRegistry');
		$mailSrv = tx_mkmailer_util_ServiceRegistry::getMailService();
		$templateObj = $mailSrv->getTemplate('t3users_sendconfirmlink');

		$markerArray = array(); $wrappedSubpartArray = array();
		$markerArray['###CONFIRMLINKURL###'] = $confirmLink->makeUrl();
		$wrappedSubpartArray['###CONFIRMLINK###'] = explode($confirmLink->getLabel(), $confirmLink->makeTag());
		
		tx_rnbase::load('tx_rnbase_util_Templates');
		$messageTxt = tx_rnbase_util_Templates::substituteMarkerArrayCached($templateObj->getContentText(), $markerArray, $subpartArray, $wrappedSubpartArray);
		$messageHtml = tx_rnbase_util_Templates::substituteMarkerArrayCached($templateObj->getContentHtml(), $markerArray, $subpartArray, $wrappedSubpartArray);

		// feUser-Marker werden mittels Marker-Klasse ersetzt
		$formatter = $configurations->getFormatter();
		$marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');
		$messageTxt = $marker->parseTemplate($messageTxt, $feuser, $formatter, $confId.'feuser.');
		$messageHtml = $marker->parseTemplate($messageHtml, $feuser, $formatter, $confId.'feuser.');

		$receiver = tx_rnbase::makeInstance('tx_mkmailer_receiver_FeUser');
		$receiver->setFeUser($feuser);

		$job = tx_rnbase::makeInstance('tx_mkmailer_mail_MailJob');
		$job->addReceiver($receiver);
		$job->setFrom($templateObj->getFromAddress());
		$job->setCCs($templateObj->getCcAddress());
		$job->setBCCs($templateObj->getBccAddress());
		$job->setSubject($templateObj->getSubject());
		$job->setContentText($messageTxt);
		$job->setContentHtml($messageHtml);
		$mailSrv->spoolMailJob($job);
	} 
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/srv/class.tx_t3users_services_email.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/srv/class.tx_t3users_services_email.php']);
}
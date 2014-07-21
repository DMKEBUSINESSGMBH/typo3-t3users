<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2014 René Nitzsche <nitzsche@das-medienkombinat.de>
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
tx_rnbase::load('tx_rnbase_util_Templates');

/**
 * Service to send emails
 * @author René Nitzsche
 * @author Holger Gebhardt
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

		$parts = explode(LF, $mailtext, 2);		// First line is subject
		$subject=trim($parts[0]);

		$mail = tx_rnbase::makeInstance('tx_rnbase_util_Mail');
		$mail->setSubject($subject);

		$mail->setFrom($emailFrom, $emailFromName);
		$mail->setTo($feuser->getEmail());
		$mail->setTextPart($mailtext);
//		$mail->setHtmlPart($mailhtml);
		$mail->send();

		$configurations->getCObj()->sendNotifyEmail($mailtext, $feuser->getEmail(), '', $emailFrom, $emailFromName, $emailReply);
	}
	/**
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param tx_rnbase_util_Link $pwLink
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 */
	public function sendResetPassword($feuser, $pwLink, $configurations, $confId = 'loginbox.') {
		if(t3lib_extMgm::isLoaded('mkmailer') && method_exists($this, 'sendResetPasswordMkMailer')) {
			// FIXME: implement!
			return $this->sendResetPasswordMkMailer($feuser, $pwLink, $configurations, $confId);
		}
		return $this->sendResetPasswordSimple($feuser, $pwLink, $configurations, $confId);

	}
	/**
	 * Sends a password reset link to the feUser
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param tx_rnbase_util_Link $pwLink
	 * @param tx_rnbase_configurations $configurations
	 */
	private function sendResetPasswordSimple($feuser, $pwLink, $configurations, $confId) {
		// Mail vorbereiten
		$template = $configurations->getLL('loginbox_reset_infomail');
		$templateHtml = trim($configurations->getLL('loginbox_reset_infomail_html'));
		$subpartArray = array();
		$wrappedSubpartArray = array();

		$token = '---';
		$pwLink->label($token);
		$linkMarker = 'RESETLINK';
		$markerArray['###'.$linkMarker . 'URL###'] = $pwLink->makeUrl(false);
		$wrappedSubpartArray['###'.$linkMarker . '###'] = explode($token, $pwLink->makeTag());
		$formatter = $configurations->getFormatter();
		$mailtext = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
		if($templateHtml)
			$mailhtml = tx_rnbase_util_Templates::substituteMarkerArrayCached($templateHtml, $markerArray, $subpartArray, $wrappedSubpartArray);

		// Jetzt noch den FeuserMarker
		$marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');
		$mailtext = $marker->parseTemplate($mailtext, $feuser, $formatter, $confId.'feuser.');
		$mailhtml = $marker->parseTemplate($mailhtml, $feuser, $formatter, $confId.'feuser.');
		$emailFrom = $configurations->get($confId.'emailFrom');
		$emailFromName = $configurations->get($confId.'emailFromName');
		$emailReply = $configurations->get($confId.'emailReply');

		$parts = explode(LF, $mailtext, 2);		// First line is subject
		$subject=trim($parts[0]);
		$mailtext=trim($parts[1]);
		if($mailhtml) {
			$parts = explode(LF, $mailhtml, 2);		// First line is subject
			$subject=trim($parts[0]);
			$mailhtml=trim($parts[1]);
		}

		$mail = tx_rnbase::makeInstance('tx_rnbase_util_Mail');
		$mail->setSubject($subject);

		$mail->setFrom($emailFrom, $emailFromName);
		$mail->setTo($feuser->getEmail());
		$mail->setTextPart($mailtext);
		$mail->setHtmlPart($mailhtml);
		$mail->send();

//		$configurations->getCObj()->sendNotifyEmail($mailtext, $feuser->getEmail(), '', $emailFrom, $emailFromName, $emailReply);
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
		$markerArray['###'.$linkMarker . 'URL###'] = $link->makeUrl(false);

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

	/**
	 * @param tx_t3users_models_feuser $feuser
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 *
	 * Beispiel Mailtemplate:
	 *
		Guten Tag ###FEUSER_NAME###
		ihre Registrierung auf Serviceoasen wurde freigegeben. Sie können sich nun unter ###FEUSER_LOGINLINK###diesem Link###FEUSER_LOGINLINK### anmelden.
		Für Fragen stehen wir Ihnen gerne jederzeit zur Verfügung.
	 *
	 */
	public function sendNotificationAboutConfirmationToFeUser(
		tx_t3users_models_feuser $feuser, tx_rnbase_configurations $configurations,
		$confId = 'showregistration.'
	) {
		$mailSrv = $this->getMkMailerMailService();
		$templateObj = $mailSrv->getTemplate('t3users_send_confirmation_notification');

		tx_rnbase::load('tx_rnbase_util_Templates');

		// feUser-Marker werden mittels Marker-Klasse ersetzt
		$formatter = $configurations->getFormatter();
		$marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');
		$messageTxt = $marker->parseTemplate(
			$templateObj->getContentText(), $feuser, $formatter, $confId.'feuser.'
		);
		$messageHtml = $marker->parseTemplate(
			$templateObj->getContentHtml(), $feuser, $formatter, $confId.'feuser.'
		);

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
	 * @return tx_mkmailer_services_Mail
	 */
	protected function getMkMailerMailService() {
		tx_rnbase::load('tx_mkmailer_util_ServiceRegistry');
		return tx_mkmailer_util_ServiceRegistry::getMailService();
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/srv/class.tx_t3users_services_email.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/srv/class.tx_t3users_services_email.php']);
}
<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2014 Rene Nitzsche (rene@system25.de)
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
 * Service for accessing user information
 *
 * @author Rene Nitzsche
 */
class tx_t3users_services_feuser extends t3lib_svbase {

	/**
	 * Find a user by mail address
	 *
	 * @param string $email
	 * @param string $pids
	 * @return tx_t3users_models_feuser
	 */
	public function getUserByEmail($email, $pids = '') {
		if (!($email && t3lib_div::validEmail($email)) )
			return false;

		$fields = array();
		$options = array();
//		$options['debug'] = 1;
		tx_t3users_search_builder::buildFeuserByEmail($fields, $email, $pids);
		$feusers = $this->search($fields, $options);

		return count($feusers) ? $feusers[0] : false;
	}

	/**
	 * Find a disabled user by mail address
	 *
	 * @param string $email
	 * @param string $pids
	 * @return tx_t3users_models_feuser
	 */
	public function getDisabledUserByEmail($email, $pids = ''){
		if (!($email && t3lib_div::validEmail($email)) )
			return false;

		$fields = array();
		$options = array();

		$options['limit'] = 1;
		// Würde es nicht auch enablefieldsbe tun?
		$options['enablefieldsoff'] = 1;
//		$options['debug'] = 1;

		$fields['FEUSER.DISABLE'][OP_EQ_INT] = 1;
		$fields['FEUSER.DELETED'][OP_EQ_INT] = 0;
		tx_t3users_search_builder::buildFeuserByEmail($fields, $email, $pids);

		$feusers = $this->search($fields, $options);

		return count($feusers) ? $feusers[0] : false;
	}

	/**
	 * Get FE user session lifetime
	 *
	 * @return int
	 * @see tslib_feUserAuth->start()
	 */
	private function getSessionLifeTime() {
		global $GLOBALS;
		$lt = $GLOBALS['TYPO3_CONF_VARS']['FE']['sessionDataLifetime'];
		if ($lt <= 0) {
			$lt = 86400;
		}
		return $lt;
	}

	/**
	 * Get the number of users or user-objects currently online
	 * This is possible only, if extension DBAL is active.
	 * @param array $config
	 * 		pids provides the users in a PID
	 * 		count (true = number of users) (false = array with user-objects)
	 * @return int / array
	 */
	public function getOnlineUsers($options = null) {
		if(tx_rnbase_util_TYPO3::isExtLoaded('dbal'))
			return 0;
		$timeout = self::getSessionLifeTime();
		if(!is_array($options)) {
			$options = array(
					'pids' => $options,
					// ursprünglich lieferte die Methode nur die Anzahl der Nutzer
					'count' => true
			);
		}
		$options['distinct'] = 1;
		$fields = array();
		$fields['FESESSION.ses_userid'][OP_GT_INT] = 0;
		if(!empty($options['pids'])) {
			$fields['FEUSER.pid'][OP_IN_INT] = $options['pids'];
		}
		$fields[SEARCH_FIELD_CUSTOM] = '(ses_tstamp+'.$timeout.' > unix_timestamp() OR is_online+'.$timeout.' > unix_timestamp())';
		return $this->search($fields, $options);
	}

	/**
	 * Check if given user is online
	 *
	 * @param int $feUserId
	 * @return int
	 */
	public function isUserOnline($feUserId){
		$timeout = self::getSessionLifeTime();
		$from = array('fe_sessions JOIN fe_users ON (ses_userid=uid)', 'fe_sessions');

		$options = array('where' => 'fe_users.uid = ' . intval($feUserId));

		if ($timeout)
			$options['where'] .= ' AND (ses_tstamp+'.$timeout.' > unix_timestamp() OR is_online+'.$timeout.' > unix_timestamp())';

		$options['enablefieldsoff'] = 1;
		$res = tx_rnbase_util_DB::doSelect('count(distinct ses_userid, ses_hashlock) as cnt',$from, $options, 0);
		return $res[0]['cnt'];
	}

	/**
	 * Encrypt the given password
	 * if an encrpyption method is actived.
	 *
	 * @param string $newPassword plaintext password
	 * @return string $newPassword encrypted password
	 */
	public function encryptPassword($newPassword) {
		if($this->useMD5()) {
			$newPassword = md5($newPassword);
		}
		elseif($this->useSaltedPasswords()) {
			$this->loadSaltedPasswordUtility();
			if (tx_saltedpasswords_div::isUsageEnabled()) {
				// generate password for db
				$cconf = tx_saltedpasswords_div::returnExtConf();
				$objPHPass = t3lib_div::makeInstance($cconf['saltedPWHashingMethod']);
				$newPassword = $objPHPass->getHashedPassword($newPassword);
			}
		}
		return $newPassword;
	}

	/**
	 * Save the given plaintext password to database. The password is encrypted
	 * if an encrpyption method is actived.
	 * @param unknown $feuser
	 * @param string $newPassword plaintext password
	 */
	public function saveNewPassword($feuser, $newPassword) {
		$newPassword = $this->encryptPassword($newPassword);
		// save password to db
		$values = array('password'=> $newPassword);
		$where = 'uid = ' . $feuser->getUid();
		tx_rnbase_util_DB::doUpdate('fe_users', $where, $values, 0);
	}
	/**
	 * Set a new randomized password for user if md5 encyption is enabled. Otherwise
	 * this methode simply retrieves the existing user password!
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param int $defaultLength
	 * @return string the new uncrypted password or false
	 */
	function createNewPassword($feuser, $defaultLength=5) {
		$ret = false;
		if($this->useMD5()) {
			require_once(t3lib_extMgm::extPath('kb_md5fepw').'class.tx_kbmd5fepw_funcs.php');
			if ($feuser->isValid())	{
				$new_password = tx_kbmd5fepw_funcs::generatePassword($defaultLength);
				$values = array('password'=> md5($new_password));
				$where = 'uid = ' . $feuser->uid;
				tx_rnbase_util_DB::doUpdate('fe_users', $where, $values, 0);
				$ret=$new_password;
			}
		} elseif($this->useSaltedPasswords()) {
			$this->loadSaltedPasswordUtility();
			if (tx_saltedpasswords_div::isUsageEnabled()) {
				$new_password = $this->generatePassword($defaultLength); 	//generate password
				$ret = $new_password; // for return in email
				// generate password for db
				$cconf = tx_saltedpasswords_div::returnExtConf();
				$objPHPass = t3lib_div::makeInstance($cconf['saltedPWHashingMethod']);
				$new_password = $objPHPass->getHashedPassword($new_password);
				// save password to db
				$values = array('password'=> $new_password);
				$where = 'uid = ' . $feuser->uid;
				tx_rnbase_util_DB::doUpdate('fe_users', $where, $values, 0);
			}
		}
		else {
			// No encryption. We load the password vom database
			// Use the getInstance method of the model, to ignore enablefields.
			$tmpUser = tx_t3users_models_feuser::getInstance($feuser->uid);
			$ret = $tmpUser->record['password'];
		}
		return $ret;
	}

	/**
	 *
	 */
	protected function loadSaltedPasswordUtility() {
		tx_rnbase::load('tx_rnbase_util_TYPO3');
		// ab 6.2 wird saltedpasswords automatisch geladen
		if (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
			return;
		}
		elseif(tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			require_once t3lib_extMgm::extPath('saltedpasswords') .
				'Classes/class.tx_saltedpasswords_div.php';
		} else {
			require_once t3lib_extMgm::extPath('saltedpasswords') .
				'classes/class.tx_saltedpasswords_div.php';
		}
	}

	/**
	 * Returns all fegroups of a feuser
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @return array[tx_t3users_models_group]
	 */
	function getFeGroups($feuser) {
		if(!$feuser->record['usergroup']) {
			return array();
		}

		$from = 'fe_groups';
		$options = array(
			'where' => 'uid IN (' . $feuser->record['usergroup'] . ') ',
			'wrapperclass' => 'tx_t3users_models_fegroup',
			'orderby' => 'title'
		);

		$rnBaseDbUtil = $this->getRnBaseDbUtil();
		return $rnBaseDbUtil::doSelect('*',$from,$options,0);
	}

	/**
	 * für tests
	 *
	 * @return tx_rnbase_util_DB
	 */
	protected function getRnBaseDbUtil() {
		return tx_rnbase_util_DB;
	}

	/**
	 * Returns all users of given fe_groups
	 *
	 * @param string $groupIds commaseparated UIDs of fe groups
	 */
	function getFeUser($groupIds) {
		$fields['FEUSER.USERGROUP'][OP_INSET_INT] = $groupIds;
		$options = array();
//		$options['debug'] = 1;
		return $this->search($fields, $options);
	}
	/**
	 * Search database for teams
	 *
	 * @param array $fields
	 * @param array $options
	 * @return array of tx_t3users_models_feuser
	 */
	function search($fields, $options) {
		tx_rnbase::load('tx_rnbase_util_SearchBase');
		$searcher = tx_rnbase_util_SearchBase::getInstance('tx_t3users_search_feuser');
		return $searcher->search($fields, $options);
	}
	/**
	 * Whether or not md5 encryption is enabled.
	 *
	 * @return boolean
	 */
	function useMD5() {
		return t3lib_extMgm::isLoaded('kb_md5fepw');
	}
	/**
	 * Whether or not saltedpasswords is enabled.
	 *
	 * @return boolean
	 */
	function useSaltedPasswords() {
		return t3lib_extMgm::isLoaded('saltedpasswords');
	}

	/**
	 * Whether or not RSA encryption is enabled.
	 */
	public function useRSA() {
		return
			($GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'] == 'rsa') &&
			t3lib_extMgm::isLoaded('rsaauth');
	}

	/**
	 * Generates a new password
	 *
	 * @param int $len
	 * @return string
	 */
	private static function generatePassword($len) {
		$pool = 'abcdefghkmnpqrstuvwxyzABCDEFGHKLMNPRSTUVWXYZ23456789.;?!-_';
		$strlen = strlen($pool)-1;
		$pw = ''; $last = ''; $i = 0;
		mt_srand(hexdec(substr(md5(microtime()), -8)) & 0x7fffffff);
		while ($i < $len) {
			$new = substr($pool, mt_rand(0, $strlen), 1);
			if ($new != $last) {
				$pw .= $new;
				$last = $new;
				$i++;
			}
		}
		return $pw;
	}

	/**
	 * Confirm a user via confirmstring. If okay the user is enabled.
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param string $confirmString
	 * @return boolean
	 */
	function confirmUser($feuser, $confirmString, &$options = array()) {
		$ret = false;
		if($feuser->record['confirmstring'] == '0') {
			// is already confirmed, so nothing to do
			// But maybe the user was manuelly deactivated from the system
			$ret = intval($feuser->record['disable']) == 0;
		}
		elseif($feuser->record['confirmstring'] == $confirmString) {
			$values = array('disable'=> 0, 'confirmstring' => 0);
			tx_rnbase_util_Misc::callHook('t3users','srv_feuser_confirmUser_before',
				array('values' => &$values, 'feuser' => $feuser), $this);
			$this->updateFeUser($feuser->uid,$values);
			$feuser->reset();

			// Müssen FE-Gruppen gesetzt werden
			if($options['successgroupsadd']) {
				$this->addFeGroups($feuser, $options['successgroupsadd']);
			}
			if($options['successgroupsremove']) {
				$this->removeFeGroup($feuser, $options['successgroupsremove']);
			}
			tx_rnbase_util_Misc::callHook('t3users','srv_feuser_confirmUser_finished',
				array('feuser' => $feuser), $this);
			$ret = true;
		}
		return $ret;
	}

	/**
	 * Add all given group UIDs from FE User
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param string $feGroupUids comma separated group uids
	 */
	public function addFeGroups(&$feuser, $feGroupIds) {
		$feGroupIds = strlen($feGroupIds) ? t3lib_div::intExplode(',', $feGroupIds) : array();
		if(!count($feGroupIds)) return; // Nothing to do

		$oldFeGroups = $feuser->record['usergroup'];
		$oldFeGroups = strlen($oldFeGroups) ? t3lib_div::intExplode(',', $oldFeGroups) : array();
		$oldFeGroupsKeys = array_flip($oldFeGroups);
		foreach ($feGroupIds as $feGroupId) {
			if(!array_key_exists($feGroupId, $oldFeGroupsKeys))
				$oldFeGroups[] = $feGroupId; // Nur einfügen, wenn noch nicht vorhanden
		}
		$oldFeGroups = implode(',', $oldFeGroups);
		$this->updateFeUser($feuser->uid, array('usergroup' => $oldFeGroups));
		$feuser->record['usergroup'] = $oldFeGroups;
	}
	/**
	 * Remove all given group UIDs from FE User
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param string $feGroupUids comma separated group uids
	 */
	public function removeFeGroup($feuser, $feGroupIds) {
		$feGroupIds = strlen($feGroupIds) ? t3lib_div::intExplode(',', $feGroupIds) : array();
		if(!count($feGroupIds)) return; // Nothing to do

		$oldFeGroups = $feuser->record['usergroup'];
		if(!strlen($oldFeGroups)) return; // Es sind gar keine Gruppen gesetzt
		$oldFeGroups = t3lib_div::intExplode(',', $oldFeGroups);
		$oldFeGroups = array_flip($oldFeGroups);
		// Jetzt die gelöschten Gruppen entfernen
		foreach($feGroupIds As $feGroupId) {
			if(array_key_exists($feGroupId, $oldFeGroups))
				unset($oldFeGroups[$feGroupId]);
		}
		// Ist noch was übrig geblieben?
		if(count($oldFeGroups)) {
			$oldFeGroups = array_flip($oldFeGroups);
			$oldFeGroups = implode(',', $oldFeGroups);
		}
		else
			$oldFeGroups = '';
		$this->updateFeUser($feuser->uid, array('usergroup' => $oldFeGroups));
		$feuser->record['usergroup'] = $oldFeGroups;
	}
	/**
	 * Update user data in database
	 *
	 * @param int $uid
	 * @param array $values
	 * @return boolean
	 * @throws tx_t3users_exceptions_User
	 */
	protected function updateFeUser($uid, $values) {
		$uid = intval($uid);
		if(!$uid) throw new tx_t3users_exceptions_User('No user id given!');
		$where = 'uid =	' . $uid;
		return tx_rnbase_util_DB::doUpdate('fe_users', $where, $values, 0);
	}

	/**
	 * Update user data in database
	 *
	 * @param tx_t3users_models_feuser $feuser
	 * @param array $values
	 * @return boolean
	 * @throws tx_t3users_exceptions_User
	 */
	public function handleUpdate(tx_t3users_models_feuser $feuser, array $values) {
		$this->updateFeUser($feuser->getUid(), $values);
		return $feuser;
	}

	/**
	 * Update user data in database
	 *
	 * @param int $uid
	 * @param array $values
	 * @return boolean
	 * @throws tx_t3users_exceptions_User
	 */
	public function updateFeUserByConfirmstring($uid, $confirmString, $data) {
		$uid = intval($uid);
		if(!$uid) throw new tx_t3users_exceptions_User('No user id given!');
		if(empty($confirmString)) throw new tx_t3users_exceptions_User('No confirmstring given!');

    	$where = 'uid =	' . $uid . ' AND confirmstring = \'' . $confirmString . '\'';

		return tx_rnbase_util_DB::doUpdate('fe_users', $where, $data, 0);
	}

	/**
	 *
	 * @param tx_t3users_models_feuser $feUser
	 * @param tx_rnbase_configurations $configurations
	 */
	public function handleForgotPass_old($feuser, $configurations, $confId){
		$newpass = $this->createNewPassword($feuser);
		$emailService = tx_t3users_util_ServiceRegistry::getEmailService();
		$emailService->sendNewPassword($feuser, $newpass, $configurations , $confId);
	}

	/**
	 * Email mit Änderungslink verschicken.
	 *
	 * @param tx_t3users_models_feuser $feUser
	 * @param tx_rnbase_configurations $configurations
	 */
	public function handleForgotPass($feuser, $configurations, $confId){
		if($configurations->get($confId.'resetPasswordMode') == 'sendpassword') {
			$this->handleForgotPass_old($feuser, $configurations, $confId);
			return;
		}
		$tz = date_default_timezone_get();
		if(!$tz)
			date_default_timezone_set('UTC');
		// Wir benötigen ein Hash und ein Ablaufdatum
		tx_rnbase::load('tx_rnbase_util_Dates');
		$data = array(
				'confirmtimeout'=> tx_rnbase_util_Dates::datetime_tstamp2mysql(strtotime('+2 days'))
		);
		$secret = $configurations->get($confId.'passwordsecret');
		$data['confirmstring'] = md5($data['confirmtimeout'].$secret.$feuser->getUid());

		$where = 'uid = ' . $feuser->uid;
		tx_rnbase_util_DB::doUpdate('fe_users', $where, $data, 0);
		// Und jetzt eine Mail mit dem Link senden
		$pwLink = $configurations->createLink();
		$pwLink->label($token);
		$pwLink->initByTS( $configurations, $confId . 'links.resetPassword.',
				array('NK_confirm' => $data['confirmstring'], 'NK_uid' => $feuser->getUid()) );
		$pwLink->setAbsUrl(TRUE);

		$emailService = tx_t3users_util_ServiceRegistry::getEmailService();
		$emailService->sendResetPassword($feuser, $pwLink, $configurations , $confId);
	}
	/**
	 * Prüft die Gültigkeit eines Confirm-Strings und liefert in diesem Fall die Instanz
	 * des feusers.
	 *
	 * @param int $uid
	 * @param string $confirmstring
	 * @return tx_t3users_models_feuser
	 */
	public function getUserForConfirm($uid, $confirmstring) {
		tx_rnbase::load('tx_t3users_models_feuser');
		$feUser = tx_t3users_models_feuser::getInstance(intval($uid));
		if ((empty($feUser) || !$feUser->isValid()) && $force)
			throw new Exception('Requested FE user not found or invalid!');

		// Ist der String korrekt?
		if($confirmstring != $feUser->record['confirmstring']) {
			tx_rnbase::load('tx_rnbase_util_Logger');
			tx_rnbase_util_Logger::info('Password reset failed on invalid confirmstring', 't3users',
				array('feuser'=>$feUser->getUid(),'stored confirm'=>$feUser->record['confirmstring'], 'submitted'=>$confirmstring));
			return null;
		}
		// Ist die Zeit noch okay
		tx_rnbase::load('tx_rnbase_util_Dates');
		$timeout = tx_rnbase_util_Dates::datetime_mysql2tstamp($feUser->record['confirmtimeout']);
		if($timeout < time()) {
			tx_rnbase::load('tx_rnbase_util_Logger');
			tx_rnbase_util_Logger::info('Password reset failed on timeout', 't3users',
				array('feuser'=>$feUser->getUid(),'stored timeout'=>$feUser->record['confirmtimeout'],
						'stored timeout2'=>$timeout, 'submitted'=> time()));
			return null;
		}
		return $feUser;
	}

	/**
	 *
	 * @param tx_t3users_models_feuser $feUser
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 */
	public function handleRequestConfirmation($feuser, $configurations, $confId){
		$emailService = tx_t3users_util_ServiceRegistry::getEmailService();

		$token = '---';
		$confirmationLink = $configurations->createLink();
		$confirmationLink->label($token);
		$confirmationLink->initByTS( $configurations, $confId . 'links.confirm.',
				array('NK_confirm' => $feuser->record['confirmstring'], 'NK_uid' => $feuser->getUid()) );
		// Eine nicht absolute URL macht einfach keinen Sinn in einer E-Mail
		$confirmationLink->setAbsUrl(TRUE);
		$emailService->sendConfirmationMail($feuser, $confirmationLink, $configurations, $confId);
	}

	/**
	 * Set a session value.
	 * The value is stored in TYPO3 session storage.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param string $extKey
	 */
	public function setSessionValue($key, $value, $extKey='t3users_common') {
		$vars = $GLOBALS['TSFE']->fe_user->getKey('ses',$extKey);
		$vars[$key] = &$value;
		$GLOBALS['TSFE']->fe_user->setKey('ses',$extKey, $vars);
	}
	/**
	 * Returns a session value
	 *
	 * @param string $key key of session value
	 * @param string $extKey optional
	 * @return mixed or null
	 */
	function getSessionValue($key, $extKey='t3users_common') {
		if(is_object($GLOBALS['TSFE']->fe_user)) {
			$vars = $GLOBALS['TSFE']->fe_user->getKey('ses',$extKey);
			return $vars[$key];
		}
		return null;
	}
	/**
	 * Removes a session value
	 *
	 * @param string $key key of session value
	 * @param string $extKey optional
	 */
	function removeSessionValue($key, $extKey='t3users_common') {
		if(is_object($GLOBALS['TSFE']->fe_user)) {
			$vars = $GLOBALS['TSFE']->fe_user->getKey('ses',$extKey);
			unset($vars[$key]);
			$GLOBALS['TSFE']->fe_user->setKey('ses',$extKey, $vars);
		}
	}

	/**
	 * Deaktiviert einen Nutzer und entwertet die E-Mail-Adresse.
	 *
	 * @param 	tx_t3users_models_feuser 	$feuser
	 * @return 	tx_t3users_models_feuser
	 */
	public function userDisable($feuser){
		$values = array(
			'email'=> $this->emailDisable($feuser->getEmail()),
			'disable' => 1,
		);
		$res = $this->updateFeUser($feuser->uid, $values);
		if($res) $feuser->reset();
		return $feuser;
	}

	/**
	 * Deaktiviert eine Mailadresse. Dies geschieht indem einfach ein zweites @-Zeichen
	 * eingefügt wird. Sollte die Adresse schon deaktiviert sein, dann wird sie ignoriert.
	 * test@domain.de => test@@domain.de
	 * test@@domain.de => test@@domain.de
	 *
	 * @param string $email
	 * @return string
	 */
	public function emailDisable($email) {
		$email = trim($email);
		// Prüfen, ob die Adresse schon disabled ist
		if(!strstr($email, '@@')) {
			$email = str_replace('@','@@',$email);
		}
		return $email;
	}
	/**
	 * Aktiviert eine Mailadresse, die vorher mit emailDisable() deaktiviert wurde. Es können auch
	 * gültige Adressen übergeben werden. Diese werden nicht verändert.
	 *
	 * @param string $email
	 * @return string
	 */
	public function emailEnable($email) {
		$email = trim($email);
		// Prüfen, ob die Adresse disabled ist
		while(strstr($email, '@@')) {
			$email = str_replace('@@','@',$email);
		}
		return $email;
	}

	/**
	 * Return either the given fe user or the currently logged in one
	 *
	 * This functionality so often it is worth being to be swapped to this method...
	 *
	 * @param tx_t3users_models_feuser	$feUser
	 * @param bool						$force	If no FE user was found, throw an exception
	 * @return tx_t3users_models_feuser
	 */
	public function getFeUserWithFallback(tx_t3users_models_feuser $feUser=null, $force=true) {
		if (is_null($feUser)) {
			// Don't use our own fe user service here to avoid infinite recursion
			// because of circular includes!
			// @TODO: getCurrent im Service integrieren!?
			tx_rnbase::load('tx_t3users_models_feuser');
			$feUser = tx_t3users_models_feuser::getCurrent();
			if ((empty($feUser) || !$feUser->isValid()) && $force)
				// @todo: eigene exception (tx_t3users_exceptions_NotLoggedIn) integrieren, damit diese ordentlich abgefangen werden kann.
				throw new Exception(
	          		'tx_t3users_services_feuser->getFeUserWithFallback(): No FE user found - nobody logged in?'
	          	);
		}
		return $feUser;
	}

}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/services/class.tx_t3users_services_feuser.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/services/class.tx_t3users_services_feuser.php']);
}

<?php

use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Utility\Logger;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2017 Rene Nitzsche (dev@dmk-ebusiness.de)
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

/**
 * Service for accessing user information.
 *
 * @author Rene Nitzsche
 */
class tx_t3users_services_feuser extends Tx_Rnbase_Service_Base implements Tx_Rnbase_Domain_Repository_InterfaceSearch
{
    /**
     * Find a user by mail address.
     *
     * @param string $email
     * @param string $pids
     *
     * @return tx_t3users_models_feuser
     */
    public function getUserByEmail($email, $pids = '')
    {
        if (!($email && Strings::validEmail($email))) {
            return false;
        }

        $fields = [];
        $options = [];
        tx_t3users_search_builder::buildFeuserByEmail($fields, $email, $pids);
        $feusers = $this->search($fields, $options);

        return count($feusers) ? $feusers[0] : false;
    }

    /**
     * Find a disabled user by mail address.
     *
     * @param string $email
     * @param string $pids
     *
     * @return tx_t3users_models_feuser
     */
    public function getDisabledUserByEmail($email, $pids = '')
    {
        if (!($email && Strings::validEmail($email))) {
            return false;
        }

        $fields = [];
        $options = [];

        $options['limit'] = 1;
        // Würde es nicht auch enablefieldsbe tun?
        $options['enablefieldsoff'] = 1;

        $fields['FEUSER.DISABLE'][OP_EQ_INT] = 1;
        $fields['FEUSER.DELETED'][OP_EQ_INT] = 0;
        tx_t3users_search_builder::buildFeuserByEmail($fields, $email, $pids);

        $feusers = $this->search($fields, $options);

        return count($feusers) ? $feusers[0] : false;
    }

    /**
     * Get FE user session lifetime.
     *
     * @return int
     *
     * @see tslib_feUserAuth->start()
     */
    public function getSessionLifeTime()
    {
        $lt = $GLOBALS['TYPO3_CONF_VARS']['FE']['sessionDataLifetime'];

        return $lt <= 0 ? 86400 : $lt;
    }

    /**
     * Get the number of users or user-objects currently online
     * This is possible only, if extension DBAL is active.
     *
     * @param array $config
     *      pids provides the users in a PID
     *      count (true = number of users) (false = array with user-objects)
     *
     * @return int / array
     *
     * @todo since TYPO3 8.7 the session backend can be in Redis and not the database, so
     * accessing FESESSION.* might have no effect
     * @todo why is only session timeout considered? Should fe_users.is_online not
     * be checked against $GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] instead
     * of $GLOBALS['TYPO3_CONF_VARS']['FE']['sessionDataLifetime']? Best would be to use
     * FrontendUserAuthentication->sessionTimeout as value as this respects the fallback
     * to 6000 seconds
     * @todo is it neccessary to check the fe_sessions table at all? Is it to get anonymous users/sessions?
     * @todo respect permalogin. When checking fe_users.is_online it is relevant if the user is
     * logged in permantly. Because if $GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] has a high value and
     * $GLOBALS['TYPO3_CONF_VARS']['FE']['permalogin'] is set to "0" users without permalogin loosing
     * there session when the close the browser. That should be considered because otherwise they
     * would could as logged in users.
     */
    public function getOnlineUsers($options = null)
    {
        if (TYPO3::isExtLoaded('dbal')) {
            return 0;
        }
        $timeout = $this->getSessionLifeTime();
        if (!is_array($options)) {
            $options = [
                    'pids' => $options,
                    // ursprünglich lieferte die Methode nur die Anzahl der Nutzer
                    'count' => true,
            ];
        }
        $options['distinct'] = 1;
        $fields = [];
        $fields['FESESSION.ses_userid'][OP_GT_INT] = 0;
        if (!empty($options['pids'])) {
            $fields['FEUSER.pid'][OP_IN_INT] = $options['pids'];
        }
        $fields[SEARCH_FIELD_CUSTOM] = '(ses_tstamp+'.$timeout.' > unix_timestamp() OR is_online+'.$timeout.' > unix_timestamp())';

        return $this->search($fields, $options);
    }

    /**
     * Check if given user is online.
     *
     * @param int $feUserId
     *
     * @return int
     *
     * @todo see todos of $this->getOnlineUsers()
     */
    public function isUserOnline($feUserId)
    {
        $timeout = $this->getSessionLifeTime();
        $from = ['fe_sessions JOIN fe_users ON (ses_userid=uid)', 'fe_sessions'];

        $options = ['where' => 'fe_users.uid = '.intval($feUserId)];

        if ($timeout) {
            $options['where'] .= ' AND (ses_tstamp+'.$timeout.' > unix_timestamp() OR is_online+'.$timeout.' > unix_timestamp())';
        }

        $options['enablefieldsoff'] = 1;
        $res = Connection::getInstance()->doSelect('count(distinct ses_userid, ses_hashlock) as cnt', $from, $options, 0);

        return $res[0]['cnt'];
    }

    /**
     * Encrypt the given password
     * if an encrpyption method is actived.
     *
     * @param string $newPassword plaintext password
     *
     * @return string $newPassword encrypted password
     */
    public function encryptPassword($newPassword)
    {
        if ($this->useSaltedPasswords()) {
            $saltedPasswordUtility = $this->getSaltedPasswordUtility();

            if ($saltedPasswordUtility::isUsageEnabled()) {
                // generate password for db
                $cconf = $saltedPasswordUtility::returnExtConf();
                $objPHPass = tx_rnbase::makeInstance($cconf['saltedPWHashingMethod']);
                $newPassword = $objPHPass->getHashedPassword($newPassword);
            }
        } elseif ($this->useMD5()) {
            $newPassword = md5($newPassword);
        }

        return $newPassword;
    }

    /**
     * Save the given plaintext password to database. The password is encrypted
     * if an encrpyption method is actived.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param string $newPassword plaintext password
     */
    public function saveNewPassword($feuser, $newPassword)
    {
        $newPassword = $this->encryptPassword($newPassword);
        // save password to db
        $values = ['password' => $newPassword];
        $where = 'uid = '.$feuser->getUid();
        Connection::getInstance()->doUpdate('fe_users', $where, $values, 0);
    }

    /**
     * Set a new randomized password for user if md5 encyption is enabled. Otherwise
     * this methode simply retrieves the existing user password!
     *
     * @param tx_t3users_models_feuser $feuser
     * @param int $defaultLength
     *
     * @return string the new uncrypted password or false
     */
    public function createNewPassword($feuser, $defaultLength = 5)
    {
        $ret = false;
        if ($this->useSaltedPasswords()) {
            $saltedPasswordUtility = $this->getSaltedPasswordUtility();
            if ($saltedPasswordUtility::isUsageEnabled()) {
                $new_password = $this->generatePassword($defaultLength);    //generate password
                $ret = $new_password; // for return in email
                // generate password for db
                $cconf = $saltedPasswordUtility::returnExtConf();
                $objPHPass = tx_rnbase::makeInstance($cconf['saltedPWHashingMethod']);
                $new_password = $objPHPass->getHashedPassword($new_password);
                // save password to db
                $values = ['password' => $new_password];
                $where = 'uid = '.$feuser->getUid();
                Connection::getInstance()->doUpdate('fe_users', $where, $values, 0);
            } else {
                tx_rnbase::load('tx_rnbase_util_Logger');
                Logger::warn(
                    'saltedpasswords soll verwendet werden, ist aber für die Verwendung'.
                    'im FE nicht aktiviert. Bitte im Extension Manager bei saltedpasswords'.
                    'die Nutzung im FE aktivieren. Sonst kann kein neues Passwort erstellt werden.',
                    't3users'
                );
            }
        } elseif ($this->useMD5()) {
            if ($feuser->isValid()) {
                $new_password = $this->generatePassword($defaultLength);
                $values = ['password' => md5($new_password)];
                $where = 'uid = '.$feuser->getUid();
                Connection::getInstance()->doUpdate('fe_users', $where, $values, 0);
                $ret = $new_password;
            }
        } else {
            // No encryption. We load the password vom database
            // Use the getInstance method of the model, to ignore enablefields.
            $tmpUser = tx_t3users_models_feuser::getInstance($feuser->getUid());
            $ret = $tmpUser->getProperty('password');
        }

        return $ret;
    }

    /**
     * @return string
     */
    protected function getSaltedPasswordUtility()
    {
        // ab 6.2 wird saltedpasswords automatisch geladen
        $utilityClass = 'TYPO3\\CMS\\Saltedpasswords\\Utility\\SaltedPasswordsUtility';

        return $utilityClass;
    }

    /**
     * Returns all fegroups of a feuser.
     *
     * @param tx_t3users_models_feuser $feuser
     *
     * @return tx_t3users_models_fegroup[]
     */
    public function getFeGroups($feuser)
    {
        if (!$feuser->getProperty('usergroup')) {
            return [];
        }

        $from = 'fe_groups';
        $options = [
            'where' => 'uid IN ('.trim($feuser->getProperty('usergroup'), ',').') ',
            'wrapperclass' => 'tx_t3users_models_fegroup',
            'orderby' => 'title',
        ];

        return $this->getRnBaseDbUtil()->doSelect('*', $from, $options, 0);
    }

    /**
     * für tests.
     *
     * @return Tx_Rnbase_Database_Connection
     */
    protected function getRnBaseDbUtil()
    {
        return Connection::getInstance();
    }

    /**
     * Returns all users of given fe_groups.
     *
     * @param string $groupIds commaseparated UIDs of fe groups
     */
    public function getFeUser($groupIds)
    {
        $fields['FEUSER.USERGROUP'][OP_INSET_INT] = $groupIds;
        $options = [];

        return $this->search($fields, $options);
    }

    /**
     * Search database for teams.
     *
     * @param array $fields
     * @param array $options
     *
     * @return array of tx_t3users_models_feuser
     */
    public function search(array $fields, array $options)
    {
        tx_rnbase::load('tx_rnbase_util_SearchBase');
        $searcher = tx_rnbase_util_SearchBase::getInstance('tx_t3users_search_feuser');

        return $searcher->search($fields, $options);
    }

    /**
     * Whether or not md5 encryption is enabled.
     *
     * @return bool
     */
    public function useMD5()
    {
        return tx_rnbase_util_Extensions::isLoaded('kb_md5fepw');
    }

    /**
     * Whether or not saltedpasswords is enabled.
     *
     * @return bool
     */
    public function useSaltedPasswords()
    {
        return tx_rnbase_util_Extensions::isLoaded('saltedpasswords');
    }

    /**
     * Whether or not RSA encryption is enabled.
     */
    public function useRSA()
    {
        return
            ('rsa' == $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel']) &&
            tx_rnbase_util_Extensions::isLoaded('rsaauth');
    }

    /**
     * Generates a new password.
     *
     * @param int $len
     *
     * @return string
     */
    private static function generatePassword($len)
    {
        $pool = 'abcdefghkmnpqrstuvwxyzABCDEFGHKLMNPRSTUVWXYZ23456789.;?!-_';
        $strlen = strlen($pool) - 1;
        $pw = '';
        $last = '';
        $i = 0;
        mt_srand(hexdec(substr(md5(microtime()), -8)) & 0x7fffffff);
        while ($i < $len) {
            $new = substr($pool, mt_rand(0, $strlen), 1);
            if ($new != $last) {
                $pw .= $new;
                $last = $new;
                ++$i;
            }
        }

        return $pw;
    }

    /**
     * Confirm a user via confirmstring. If okay the user is enabled.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param string $confirmString
     *
     * @return bool
     */
    public function confirmUser($feuser, $confirmString, &$options = [])
    {
        $ret = false;
        if ('0' == $feuser->getProperty('confirmstring')) {
            // is already confirmed, so nothing to do
            // But maybe the user was manuelly deactivated from the system
            $ret = 0 == intval($feuser->getProperty('disable'));
        } elseif ($feuser->getProperty('confirmstring') == $confirmString) {
            $values = ['disable' => 0, 'confirmstring' => 0];
            tx_rnbase_util_Misc::callHook(
                't3users',
                'srv_feuser_confirmUser_before',
                ['values' => &$values, 'feuser' => $feuser],
                $this
            );
            $this->updateFeUser($feuser->getUid(), $values);
            $feuser->reset();

            // Müssen FE-Gruppen gesetzt werden
            if ($options['successgroupsadd']) {
                $this->addFeGroups($feuser, $options['successgroupsadd']);
            }
            if ($options['successgroupsremove']) {
                $this->removeFeGroup($feuser, $options['successgroupsremove']);
            }
            if ($options['notifyUserAboutConfirmation']) {
                tx_t3users_util_ServiceRegistry::getEmailService()
                    ->sendNotificationAboutConfirmationToFeUser($feuser, $options['configurations']);
            }
            tx_rnbase_util_Misc::callHook(
                't3users',
                'srv_feuser_confirmUser_finished',
                ['feuser' => $feuser, 'options' => $options],
                $this
            );

            $ret = true;
        } else {
            tx_rnbase::load('tx_rnbase_util_Logger');
            tx_rnbase_util_Logger::notice(
                'confirmation failed for feuser with uid '.$feuser->getUid(),
                't3users',
                ['submitted' => $confirmString, 'stored' => $feuser->getProperty('confirmstring')]
            );
        }

        return $ret;
    }

    /**
     * Add all given group UIDs from FE User.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param string $feGroupUids comma separated group uids
     */
    public function addFeGroups(&$feuser, $feGroupIds)
    {
        $feGroupIds = strlen($feGroupIds) ? Tx_Rnbase_Utility_Strings::intExplode(',', $feGroupIds) : [];
        if (!count($feGroupIds)) {
            return;
        } // Nothing to do

        $oldFeGroups = $feuser->getProperty('usergroup');
        $oldFeGroups = strlen($oldFeGroups) ? Tx_Rnbase_Utility_Strings::intExplode(',', $oldFeGroups) : [];
        $oldFeGroupsKeys = array_flip($oldFeGroups);
        foreach ($feGroupIds as $feGroupId) {
            if (!array_key_exists($feGroupId, $oldFeGroupsKeys)) {
                $oldFeGroups[] = $feGroupId;
            } // Nur einfügen, wenn noch nicht vorhanden
        }
        $oldFeGroups = implode(',', $oldFeGroups);
        $this->updateFeUser($feuser->getUid(), ['usergroup' => $oldFeGroups]);
        $feuser->setProperty('usergroup', $oldFeGroups);
    }

    /**
     * Remove all given group UIDs from FE User.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param string $feGroupUids comma separated group uids
     */
    public function removeFeGroup($feuser, $feGroupIds)
    {
        $feGroupIds = strlen($feGroupIds) ? Tx_Rnbase_Utility_Strings::intExplode(',', $feGroupIds) : [];
        if (!count($feGroupIds)) {
            return;
        } // Nothing to do

        $oldFeGroups = $feuser->getProperty('usergroup');
        if (!strlen($oldFeGroups)) {
            return;
        } // Es sind gar keine Gruppen gesetzt
        $oldFeGroups = Tx_Rnbase_Utility_Strings::intExplode(',', $oldFeGroups);
        $oldFeGroups = array_flip($oldFeGroups);
        // Jetzt die gelöschten Gruppen entfernen
        foreach ($feGroupIds as $feGroupId) {
            if (array_key_exists($feGroupId, $oldFeGroups)) {
                unset($oldFeGroups[$feGroupId]);
            }
        }
        // Ist noch was übrig geblieben?
        if (count($oldFeGroups)) {
            $oldFeGroups = array_flip($oldFeGroups);
            $oldFeGroups = implode(',', $oldFeGroups);
        } else {
            $oldFeGroups = '';
        }
        $this->updateFeUser($feuser->getUid(), ['usergroup' => $oldFeGroups]);
        $feuser->setProperty('usergroup', $oldFeGroups);
    }

    /**
     * Update user data in database.
     *
     * @param int $uid
     * @param array $values
     *
     * @return bool
     *
     * @throws tx_t3users_exceptions_User
     */
    protected function updateFeUser($uid, $values)
    {
        $uid = intval($uid);
        if (!$uid) {
            throw new tx_t3users_exceptions_User('No user id given!');
        }
        $where = 'uid = '.$uid;

        return Tx_Rnbase_Database_Connection::getInstance()->doUpdate('fe_users', $where, $values, 0);
    }

    /**
     * Update user data in database.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param array $values
     *
     * @return bool
     *
     * @throws tx_t3users_exceptions_User
     */
    public function handleUpdate(tx_t3users_models_feuser $feuser, array $values)
    {
        $this->updateFeUser($feuser->getUid(), $values);

        return $feuser;
    }

    /**
     * Update user data in database.
     *
     * @param int $uid
     * @param array $values
     *
     * @return bool
     *
     * @throws tx_t3users_exceptions_User
     */
    public function updateFeUserByConfirmstring($uid, $confirmString, $data)
    {
        $uid = intval($uid);
        if (!$uid) {
            throw new tx_t3users_exceptions_User('No user id given!');
        }
        if (empty($confirmString)) {
            throw new tx_t3users_exceptions_User('No confirmstring given!');
        }

        $where = 'uid = '.$uid.' AND confirmstring = \''.$confirmString.'\'';

        return $this->getRnBaseDbUtil()->doUpdate('fe_users', $where, $data, 0);
    }

    /**
     * @param tx_t3users_models_feuser $feUser
     * @param \Sys25\RnBase\Configuration\Processor $configurations
     */
    public function handleForgotPass_old($feuser, $configurations, $confId)
    {
        $newpass = $this->createNewPassword($feuser);
        $emailService = tx_t3users_util_ServiceRegistry::getEmailService();
        $emailService->sendNewPassword($feuser, $newpass, $configurations, $confId);
    }

    /**
     * Email mit Änderungslink verschicken.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param \Sys25\RnBase\Configuration\Processor $configurations
     */
    public function handleForgotPass($feuser, $configurations, $confId)
    {
        if ('sendpassword' == $configurations->get($confId.'resetPasswordMode')) {
            $this->handleForgotPass_old($feuser, $configurations, $confId);

            return;
        }
        $tz = date_default_timezone_get();
        if (!$tz) {
            date_default_timezone_set('UTC');
        }

        // Wir benötigen ein Hash und ein Ablaufdatum
        $data = [
            'confirmtimeout' => tx_rnbase_util_Dates::datetime_tstamp2mysql(strtotime('+2 days')),
        ];
        $secret = $configurations->get($confId.'passwordsecret');
        $data['confirmstring'] = md5($data['confirmtimeout'].$secret.$feuser->getUid());

        $where = 'uid = '.$feuser->getUid();
        Connection::getInstance()->doUpdate('fe_users', $where, $data, 0);
        // Und jetzt eine Mail mit dem Link senden
        $pwLink = $configurations->createLink();
        $pwLink->label(md5(microtime()));
        $pwLink->initByTS(
            $configurations,
            $confId.'links.resetPassword.',
            ['NK_confirm' => $data['confirmstring'], 'NK_uid' => $feuser->getUid()]
        );
        $emailService = tx_t3users_util_ServiceRegistry::getEmailService();
        $emailService->sendResetPassword($feuser, $pwLink, $configurations, $confId);
    }

    /**
     * Prüft die Gültigkeit eines Confirm-Strings und liefert in diesem Fall die Instanz
     * des feusers.
     *
     * @param int $uid
     * @param string $confirmstring
     *
     * @return tx_t3users_models_feuser
     */
    public function getUserForConfirm($uid, $confirmstring)
    {
        $feUser = tx_t3users_models_feuser::getInstance(intval($uid));
        if ((empty($feUser) || !$feUser->isValid())) {
            throw new Exception('Requested FE user not found or invalid!');
        }

        // Ist der String korrekt?
        if ($confirmstring != $feUser->getProperty('confirmstring')) {
            Logger::info(
                'Password reset failed on invalid confirmstring',
                't3users',
                ['feuser' => $feUser->getUid(), 'stored confirm' => $feUser->getProperty('confirmstring'), 'submitted' => $confirmstring]
            );

            return null;
        }
        // Ist die Zeit noch okay
        $timeout = tx_rnbase_util_Dates::datetime_mysql2tstamp($feUser->getProperty('confirmtimeout'));
        if ($timeout < time()) {
            Logger::info(
                'Password reset failed on timeout',
                't3users',
                ['feuser' => $feUser->getUid(), 'stored timeout' => $feUser->getProperty('confirmtimeout'),
                'stored timeout2' => $timeout,
                'submitted' => $GLOBALS['EXEC_TIME'], ]
            );

            return null;
        }

        return $feUser;
    }

    /**
     * @param tx_t3users_models_feuser $feUser
     * @param \Sys25\RnBase\Configuration\Processor $configurations
     * @param string $confId
     */
    public function handleRequestConfirmation($feuser, $configurations, $confId)
    {
        $emailService = tx_t3users_util_ServiceRegistry::getEmailService();

        $token = '---';
        $confirmationLink = $configurations->createLink();
        $confirmationLink->label($token);
        $confirmationLink->initByTS(
            $configurations,
            $confId.'links.confirm.',
            ['NK_confirm' => $feuser->getProperty('confirmstring'), 'NK_uid' => $feuser->getUid()]
        );
        // Eine nicht absolute URL macht einfach keinen Sinn in einer E-Mail
        $confirmationLink->setAbsUrl(true);
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
    public function setSessionValue($key, $value, $extKey = 't3users_common')
    {
        $vars = TYPO3::getFEUser()->getKey('ses', $extKey);
        $vars[$key] = &$value;
        TYPO3::getFEUser()->setKey('ses', $extKey, $vars);
    }

    /**
     * Returns a session value.
     *
     * @param string $key key of session value
     * @param string $extKey optional
     *
     * @return mixed or null
     */
    public function getSessionValue($key, $extKey = 't3users_common')
    {
        if (is_object($GLOBALS['TSFE']->fe_user)) {
            $vars = $GLOBALS['TSFE']->fe_user->getKey('ses', $extKey);

            return $vars[$key];
        }

        return null;
    }

    /**
     * Removes a session value.
     *
     * @param string $key key of session value
     * @param string $extKey optional
     */
    public function removeSessionValue($key, $extKey = 't3users_common')
    {
        if (is_object(TYPO3::getFEUser())) {
            $vars = TYPO3::getFEUser()->getKey('ses', $extKey);
            unset($vars[$key]);
            TYPO3::getFEUser()->setKey('ses', $extKey, $vars);
        }
    }

    /**
     * Deaktiviert einen Nutzer und entwertet die E-Mail-Adresse.
     *
     * @param tx_t3users_models_feuser $feuser
     *
     * @return tx_t3users_models_feuser
     */
    public function userDisable($feuser)
    {
        $values = [
            'email' => $this->emailDisable($feuser->getEmail()),
            'disable' => 1,
        ];
        $res = $this->updateFeUser($feuser->getUid(), $values);
        if ($res) {
            $feuser->reset();
        }

        return $feuser;
    }

    /**
     * Deaktiviert eine Mailadresse. Dies geschieht indem einfach ein zweites @-Zeichen
     * eingefügt wird. Sollte die Adresse schon deaktiviert sein, dann wird sie ignoriert.
     * test@domain.de => test@@domain.de
     * test@@domain.de => test@@domain.de.
     *
     * @param string $email
     *
     * @return string
     */
    public function emailDisable($email)
    {
        $email = trim($email);
        // Prüfen, ob die Adresse schon disabled ist
        if (!strstr($email, '@@')) {
            $email = str_replace('@', '@@', $email);
        }

        return $email;
    }

    /**
     * Aktiviert eine Mailadresse, die vorher mit emailDisable() deaktiviert wurde. Es können auch
     * gültige Adressen übergeben werden. Diese werden nicht verändert.
     *
     * @param string $email
     *
     * @return string
     */
    public function emailEnable($email)
    {
        $email = trim($email);
        // Prüfen, ob die Adresse disabled ist
        while (strstr($email, '@@')) {
            $email = str_replace('@@', '@', $email);
        }

        return $email;
    }

    /**
     * Return either the given fe user or the currently logged in one.
     *
     * This functionality so often it is worth being to be swapped to this method...
     *
     * @param tx_t3users_models_feuser  $feUser
     * @param bool                      $force  If no FE user was found, throw an exception
     *
     * @return tx_t3users_models_feuser
     */
    public function getFeUserWithFallback(tx_t3users_models_feuser $feUser = null, $force = true)
    {
        if (is_null($feUser)) {
            // Don't use our own fe user service here to avoid infinite recursion
            // because of circular includes!
            // @TODO: getCurrent im Service integrieren!?
            $feUser = tx_t3users_models_feuser::getCurrent();
            if ((empty($feUser) || !$feUser->isValid()) && $force) {
                // @todo: eigene exception (tx_t3users_exceptions_NotLoggedIn) integrieren, damit diese ordentlich abgefangen werden kann.
                throw new Exception('tx_t3users_services_feuser->getFeUserWithFallback(): No FE user found - nobody logged in?');
            }
        }

        return $feUser;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return void
     *
     * @todo es muss entweder $GLOBALS['TSFE']->fe_user->checkPid = FALSE gesetzt werden oder eine pid übergeben
     * werden. ALternativ kann vor dem Aufruf auch selbst $_POST['pid'] gesetzt werden.
     */
    public function loginFrontendUserByUsernameAndPassword($username, $password)
    {
        // die Daten können nur aus einem flachen Array gelesen werden
        $_POST['user'] = $username;
        $_POST['pass'] = $password;
        $_POST['logintype'] = 'login';

        TYPO3::getFEUser()->start();
    }
}

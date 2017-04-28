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



require_once(tx_rnbase_util_Extensions::extPath('rn_base') . 'model/class.tx_rnbase_model_base.php');

/**
 * Model for fe_user.
 */
class tx_t3users_models_feuser extends tx_rnbase_model_base
{
    private static $instances = array();
    private $bEnableFieldsOff = false;

    public function getTableName()
    {
        return 'fe_users';
    }

    public function __construct($rowOrUid, $bEnableFieldsOff = false)
    {
        $this->bEnableFieldsOff = $bEnableFieldsOff;
        $this->init($rowOrUid);
    }
    public function tx_t3users_models_feuser($rowOrUid, $bEnableFieldsOff = false)
    {
        $this->bEnableFieldsOff = $bEnableFieldsOff;
        $this->init($rowOrUid);
    }
    /**
     * Wir nutzen nicht die initialisierung von rnbase,
     * da dort enablefields mit geprüft werden.
     * Wir wollen aber auch immer nutzer welche auf disabled stehen.
     *
     * @param mixed $rowOrUid
     */
    public function init($rowOrUid = null)
    {
        if (!$this->bEnableFieldsOff || is_array($rowOrUid)) {
            parent::init($rowOrUid);
        } else {
            $this->uid = $rowOrUid;
            if ($this->getTableName()) {
                $options = array();
                $options['where'] = 'uid='.intval($this->uid);
                $options['enablefieldsoff'] = true;
                $result = tx_rnbase_util_DB::doSelect('*', $this->getTableName(), $options);
                $this->record =  count($result) > 0 ? $result[0] : array('uid' => $rowOrUid);
            }
            // Der Record sollte immer ein Array sein
            $this->record = is_array($this->record) ? $this->record : array();
        }
    }

    /**
     * Liefert die Instance mit der übergebenen UID. Die Daten werden gecached, so daß
     * bei zwei Anfragen für die selbe UID nur ein DB Zugriff erfolgt.
     *
     * @param int $uid
     * @return tx_t3users_models_feuser
     */
    public static function getInstance($data = null)
    {
        $uid = (int) $data;
        if (!$uid) {
            throw new Exception('No uid for fe_user given!');
        }
        if (!is_object(self::$instances[$uid])) {
            self::$instances[$uid] = new tx_t3users_models_feuser($uid, true);
        }

        return self::$instances[$uid];
    }

    /**
     * Löscht die Instanz mit der Uid
     * Eigentlich nur für Tests gut aber auch wenn der User geändert wurde
     * und neu geladen werden muss
     *
     * @TODO Sobald mind. PHP 5.3 auf so gut wie allen System installiert ist,
     * kann man das Feature backupStaticVariables von PHPUnit nutzen und brauch
     * solche Funktionen nicht mehr
     *
     * Natürlich ist das eigentlich ein No-Go so eine Funktion zu schreiben
     * aber damit die Tests weiter laufen, gibt es gerade keine andere vernünftige
     * Möglichkeit. Außer jemand schreibt einen StreamWrapper wie in
     * http://blog.fifteen3.com/2009/04/reset-private-static-variables-using.html
     * beschrieben oder PHP 5.3 ist immer da. Man könnte natürlich
     * auch für alle testzwecke User mit eigenen Uids nutzen aber
     * das würde zu unwahrscheinlich vielen und schwer pflegbaren
     * testdaten führen.
     *
     * @param int $uid
     */
    public static function unsetInstance($uid)
    {
        self::$instances[$uid] = null;
    }
    /**
     * Liefert die Instanz des aktuell angemeldeten Users oder false
     * @return tx_t3users_models_feuser
     */
    public static function getCurrent()
    {
        global $TSFE;
        $userId = $TSFE->fe_user->user['uid'];

        return intval($userId) ? self::getInstance($userId) : false;
    }
    /**
     * Returns all usergroups
     *
     * @return array of tx_t3users_models_fegroup or empty array
     */
    public function getGroups()
    {
        $usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();

        return $usrSrv->getFeGroups($this);
    }
    /**
     * Whether or not user details should be shown
     *
     * @return bool
     */
    public function isDetailsEnabled()
    {
        return true;
    }
    /**
     * Whether or not user is disabled in FE
     * @return bool
     */
    public function isDisabled()
    {
        return intval($this->record['disable']) > 0;
    }
    /**
     * Returns the users email address
     * @return string
     */
    public function getEmail()
    {
        return $this->record['email'];
    }

    /**
     * Whether or not user has an active session
     */
    public function isSessionActive()
    {
        return tx_t3users_util_ServiceRegistry::getFeUserService()->isUserOnline($this->getUid());
    }

    /**
     * Prüft ob der User der gegebenen Gruppe angehört
     * @param int $groupUid
     */
    public function isInGroup($groupUid)
    {
        foreach ($this->getGroups() as $value) {
            $groups[$value->getUid()] = $value->getUid();//alle Gruppen IDs sammeln
        }

        return isset($groups[$groupUid]);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->record['username'];
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/models/class.tx_t3users_models_feuser.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/models/class.tx_t3users_models_feuser.php']);
}

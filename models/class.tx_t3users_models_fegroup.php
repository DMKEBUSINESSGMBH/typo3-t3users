<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_model_base');

/**
 * Model for fe_group.
 */
class tx_t3users_models_fegroup extends tx_rnbase_model_base {
  private static $instances = array();


  function getTableName(){return 'fe_groups';}

  /**
   * Liefert die Instance mit der übergebenen UID. Die Daten werden gecached, so daß
   * bei zwei Anfragen für die selbe UID nur ein DB Zugriff erfolgt.
   *
   * @param int $uid
   * @return tx_t3users_models_feuser
   */
  static function getInstance($uid) {
    $uid = intval($uid);
    if(!uid) throw new Exception('No uid for fe_group given!');
    if(!is_object(self::$instances[$uid])) {
      self::$instances[$uid] = new tx_t3users_models_fegroup($uid);
    }
    return self::$instances[$uid];
  }

  /**
   * Returns all users of this group
   *
   * @return array[tx_t3users_models_feuser]
   */
  function getUsers() {
  	$srv = tx_t3users_util_ServiceRegistry::getFeUserService();
  	return $srv->getFeUser($this->uid);
  }
  /**
   * Returns the group name
   *
   * @return string
   */
  function getTitle() {
  	return $this->record['title'];
  }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/models/class.tx_t3users_models_fegroup.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/models/class.tx_t3users_models_fegroup.php']);
}

?>
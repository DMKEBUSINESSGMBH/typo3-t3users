<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche (dev@dmk-ebusiness.de)
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


tx_rnbase::load('tx_rnbase_model_base');
tx_rnbase::load('tx_t3users_models_feuser');

interface tx_t3users_models_ILog {
	/**
	 * Returns the uid of current feuser
	 * @return int
	 */
	function getFEUserUid();
	/**
	 * Returns the type of action. This is a unique type string
	 * @return string
	 */
	function getType();
	/**
	 * Returns the uid of a used record.
	 * @return int
	 */
	function getRecUid();
	/**
	 * Returns the uid of the used records table.
	 * @return string
	 */
	function getRecTable();
	/**
	 * Optional additional data string
	 * @return string or array
	 */
	function getData();
	/**
	 * Optional timestamp
	 * @return string Format: Y-m-d H:i:s
	 */
	function getTimeStamp();
}


/**
 * Model for fe_user.
 */
class tx_t3users_models_log extends tx_rnbase_model_base implements tx_t3users_models_ILog {
  function getTableName(){return 'tx_t3users_log';}

	function getFEUserUid() {
		return $this->record['feuser'];
	}
	/**
	 * Liefert den FEUser
	 *
	 * @return tx_t3users_models_feuser
	 */
	function getFEUser() {
		return tx_t3users_models_feuser::getInstance($this->record['feuser']);
	}
	/**
	 * Returns the type of action. This is a unique type string
	 * @return string
	 */
	function getType(){
		return $this->record['typ'];
	}
	function getRecUid(){
		return intval($this->record['recuid']);
	}
	function getRecTable(){
		return (string) $this->record['rectable'];
	}
	function getData(){
		return $this->record['data'];
	}
	/**
	 * Optional timestamp
	 * @return string Format: Y-m-d H:i:s
	 */
	function getTimeStamp() {
		return $this->record['tstamp'];
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/models/class.tx_t3users_models_log.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/models/class.tx_t3users_models_log.php']);
}
?>
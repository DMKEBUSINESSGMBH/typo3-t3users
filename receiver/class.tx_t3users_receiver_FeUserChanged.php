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

tx_rnbase::load('tx_mkmailer_receiver_FeUser');
/**
 * Implementierung für einen Mailempfänger vom Typ FeUser.
 * Ändert ein User seine Email dann wird beim bestätigen
 * die bereits bestätigte Mail erneut an die geänderten Daten geschickt (
 * liegt am mail log da die mail dort für die alte aber nicht die
 * geänderte adresse drin steht aber der receiver der mail nun eine
 * neue adresse hat. ergo wird die mail nochmals verschickt.
 * also brauchen wir einen eigen receiver der immer die ursprüngliche
 * adresse zurück gibt statt die aktuelle des users
 */
class tx_t3users_receiver_FeUserChanged extends tx_mkmailer_receiver_FeUser {
	
	public function getValueString() {
		return is_object($this->obj) ? $this->obj->uid . ',' . (!empty($this->email) ? $this->email : $this->obj->record['email'] ) : '';
	}
	
	public function setValueString($value) {
		$aValues = t3lib_div::trimExplode(',',$value,true);
		tx_rnbase::load('tx_t3users_models_feuser');
		$this->setFeUser(tx_t3users_models_feuser::getInstance(intval($aValues[0])));
		//die neue, geänderte Email Adresse im Empfänger setzen damit die
		//Mail nicht 2 mal verschickt wird an die neue Adresse
		$this->email = $aValues[1];
	}
	
	protected function getEmail() {
		if(empty($this->email)) return false;
		//else
		return $this->email;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/receiver/class.tx_t3users_receiver_FeUserChanged.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/receiver/class.tx_t3users_receiver_FeUserChanged.php']);
}
?>
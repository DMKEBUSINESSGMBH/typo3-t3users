<?php
/**
 * 	@package tx_t3users
 *  @subpackage tx_t3users_mod
 *
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
 */

/**
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_t3users_mod_decorator_Base');

/**
 * Es gibt zunächst noch nichts spezifisches zu tun.
 * Alles passiert bisher in tx_t3users_mod_decorator_Base
 *
 * @package tx_t3users
 *  @subpackage tx_t3users_mod
 */
class tx_t3users_mod_decorator_FeUser extends tx_t3users_mod_decorator_Base {

	/**
	 *
	 * @param 	string 					$value
	 * @param 	string 					$colName
	 * @param 	array 					$record
	 * @param 	tx_rnbase_model_base 	$item
	 */
	public function format($value, $colName, $record, tx_rnbase_model_base $item) {
		return parent::format($value, $colName, $record, $item);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/mod/decorator/class.tx_t3users_mod_decorator_FeUser.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/mod/decorator/class.tx_t3users_mod_decorator_FeUser.php']);
}
?>
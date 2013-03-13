<?php
/**
 * 	@package tx_t3users
 *  @subpackage tx_t3users_mod
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
tx_rnbase::load('tx_rnbase_mod_IDecorator');

/**
 * Diese Klasse ist für die Darstellung von Elementen im Backend verantwortlich.
 *
 * @package tx_t3users
 * @subpackage tx_t3users_mod
 */
class tx_t3users_mod_decorator_Base implements tx_rnbase_mod_IDecorator{

	/**
	 *
	 * @param 	tx_rnbase_mod_IModule 	$mod
	 */
	public function __construct(tx_rnbase_mod_IModule $mod) {
		$this->mod = $mod;
	}


	/**
	 *
	 * @param 	string 					$value
	 * @param 	string 					$colName
	 * @param 	array 					$record
	 * @param 	tx_rnbase_model_base 	$item
	 */
	public function format($value, $colName, $record, tx_rnbase_model_base $item) {
		$ret = $value;

		switch ($colName) {
			case 'crdate':
			case 'tstamp':
				$ret = strftime('%d.%m.%y %H:%M:%S', intval($ret));

				break;

			case 'actions':
				$ret .= $this->getActions($item, $this->getActionOptions($item));
				break;

			default:
				$ret = $ret;
				break;
		}

		return $ret;
	}

	/**
	 * Liefert die möglichen Optionen für die actions
	 * @param tx_rnbase_model_base $item
	 * @return array
	 */
	protected function getActionOptions($item = null) {
		$cols = array(
			'edit' => '',
			'hide' => '',
		);

		$userIsAdmin = is_object($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER']->isAdmin() : 0;
		//admins dürfen auch löschen
		if ($userIsAdmin)
			$cols['remove'] = '';

		return $cols;
	}


	/**
	 * Returns the module
	 * @return tx_rnbase_mod_IModule
	 */
	protected function getModule() {
		return $this->mod;
	}

	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 *
	 * @return 	tx_rnbase_util_FormTool
	 */
	protected function getFormTool() {
		return $this->mod->getFormTool();
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/mod/decorator/class.tx_t3users_mod_decorator_Base.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/mod/decorator/class.tx_t3users_mod_decorator_Base.php']);
}

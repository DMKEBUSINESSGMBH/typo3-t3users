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
tx_rnbase::load('tx_rnbase_mod_base_Lister');

/**
 * Hilfsklassen um nach Landkreisen im BE zu suchen
 *
 * @package tx_t3users
 * @subpackage tx_t3users_mod
 */
class tx_t3users_mod_lister_FeUser extends tx_rnbase_mod_base_Lister {

/**
	 * Liefert die Funktions-Id
	 */
	public function getSearcherId() {
		return 'feuser';
	}

	/**
	 * Liefert den Service.
	 *
	 * @return tx_t3users_srv_Base
	 */
	protected function getService() {
		return tx_t3users_util_ServiceRegistry::getFeUserService();
	}

	/**
	 * (non-PHPdoc)
	 * @see tx_t3users_mod_searcher_abstractBase::getSearchColumns()
	 */
	protected function getSearchColumns() {
		return array('FEUSER.uid, FEUSER.username, FEUSER.first_name,
				FEUSER.last_name, FEUSER.email, FEUSER.address,
				FEUSER.zip, FEUSER.company, FEUSER.www,
				FEUSER.telephone, FEUSER.city');
	}

	/**
	 * Liefert die Spalten für den Decorator.
	 * @param 	tx_t3users_mod_decorator_Base 	$oDecorator
	 * @return 	array
	 */
	protected function getColumns(&$oDecorator = null) {
		$aDecorator = parent::getColumns($oDecorator);
		$sTableAlias = 'FEUSER.';
		$aDecorator['first_name'] = array(
				'title' => 'label_tableheader_firstname',
				'decorator' => &$oDecorator,
				'sortable' => $sTableAlias
		);
		$aDecorator['last_name'] = array(
				'title' => 'label_tableheader_lastname',
				'decorator' => &$oDecorator,
				'sortable' => $sTableAlias
		);
		return $aDecorator;
	}

	/**
	 *
	 * @return tx_rnbase_mod_IDecorator
	 */
	protected function createDefaultDecorator() {
		return tx_rnbase::makeInstance('tx_t3users_mod_decorator_FeUser', $this->getModule());
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/mod/lister/class.tx_t3users_mod_lister_FeUser.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/mod/lister/class.tx_t3users_mod_lister_FeUser.php']);
}
?>
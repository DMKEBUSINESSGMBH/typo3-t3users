<?php
/**
 *
 *  @package tx_t3users
 *  @subpackage tx_t3users_mod1
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
tx_rnbase::load('Tx_Rnbase_Backend_Utility');
tx_rnbase::load('tx_rnbase_parameters');
tx_rnbase::load('tx_rnbase_util_TCA');

/**
 * Die Klasse stellt Auswahlmenus zur Verfügung
 *
 * @package tx_t3users
 * @subpackage tx_t3users_mod1
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 */
class tx_t3users_mod_util_Selector {

	/**
	 * @var 	tx_rnbase_mod_IModule
	 */
	private $mod;
	/**
	 * @var 	tx_rnbase_util_FormTool
	 */
	private $formTool;

	/**
	 * Initialisiert das Objekt mit dem Template und der Modul-Config.
	 */
	public function init(tx_rnbase_mod_IModule $module){
		$this->mod = $module;
		$this->formTool = $this->mod->getFormTool();
	}



	/**
	 * Gibt einen selector mit den elementen im gegebenen array zurück
	 * @param array $aItems Array mit den werten der Auswahlbox
	 * @param string $sDefId ID-String des Elements
	 * @param array $aData enthält die Formularelement für die Ausgabe im Screen. Keys: selector, label
	 * @param array $aOptions zusätzliche Optionen: label, id
	 * @return string selected item
	 */
	protected function showSelectorByArray($aItems, $sDefId, &$aData, $aOptions=array()) {
		$pid = isset($aOptions['pid']) && $aOptions['pid'] ? $aOptions['pid'] : 0;
		$id = isset($aOptions['id']) && $aOptions['id'] ? $aOptions['id'] : $sDefId;

		$selectedItem = array_key_exists('forcevalue', $aOptions) ? $aOptions['forcevalue'] : $this->getValueFromModuleData($id);

		// Build select box items
		$aData['selector'] = Tx_Rnbase_Backend_Utility::getFuncMenu(
			$pid, 'SET['.$id.']', $selectedItem, $aItems
		);

		//label
		$aData['label'] = $aOptions['label'];

		// as the deleted fe users have always to be hidden the function returns always false
		//@todo wozu die alte abfrage? return $defId==$id ? false : $selectedItem;
		return $selectedItem;
	}
	/**
	 * Gibt einen selector mit den elementen im gegebenen array zurück
	 * @return string selected item
	 */
	protected function showSelectorByTCA($sDefId, $table, $column, &$aData, $aOptions=array()) {
		$items = array();
		if(is_array($aOptions['additionalItems'])) {
			$items = $aOptions['additionalItems'];
		}
		tx_rnbase_util_TCA::loadTCA($table);
		if(is_array($GLOBALS['TCA'][$table]['columns'][$column]['config']['items']))
			foreach($GLOBALS['TCA'][$table]['columns'][$column]['config']['items'] As $item){
				$items[$item[1]] = $GLOBALS['LANG']->sL($item[0]);
			}
		return $this->showSelectorByArray($items, $sDefId, $aData, $aOptions);
	}

	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 *
	 * @return 	tx_rnbase_mod_IModule
	 */
	protected function getModule() {
		return $this->mod;
	}
	/**
	 *
	 * @return tx_rnbase_util_FormTool
	 */
	protected function getFormTool() {
		return $this->getModule()->getFormTool();
	}

	/**
	 * Return requested value from module data
	 *
	 * @param 	string $key
	 * @return 	mixed
	 * @deprecated tx_rnbase_mod_Util::getModuleValue verwenden
	 */
	public function getValueFromModuleData($key) {
		// Fetch selected company trade
		$modData = Tx_Rnbase_Backend_Utility::getModuleData(array ($key => ''),tx_rnbase_parameters::getPostOrGetParameter('SET'),$this->getModule()->getName());
		if (isset($modData[$key])) return $modData[$key];
		// else
		return null;
	}

	/**
	 * Setzt einen Wert in den Modul Daten. Dabei werden die bestehenden
	 * ergänzt oder ggf. überschrieben
	 *
	 * @param 	array $aModuleData
	 * @return 	void
	 */
	public function setValueToModuleData($sModuleName, $aModuleData = array()) {
		$aExistingModuleData = $GLOBALS['BE_USER']->getModuleData($sModuleName);
		if(!empty($aModuleData))
			foreach ($aModuleData as $sKey => $mValue)
				$aExistingModuleData[$sKey] = $mValue;
		$GLOBALS['BE_USER']->pushModuleData($sModuleName,$aExistingModuleData);
	}


}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/util/class.tx_t3users_mod_util_Selector.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/util/class.tx_t3users_mod_util_Selector.php']);
}

?>
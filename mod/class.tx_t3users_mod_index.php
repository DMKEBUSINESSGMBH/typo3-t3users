<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche <dev@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_t3users_util_LoginAsFEUser');
tx_rnbase::load('Tx_Rnbase_Backend_AbstractFunctionModule');
tx_rnbase::load('tx_rnbase_mod_Tables');

// Mögliche Icons im BE für die Funktion doc->icons()
define('ICON_OK', -1);
define('ICON_INFO', 1);
define('ICON_WARN', 2);
define('ICON_FATAL', 3);

/**
 * Module extension (addition to function menu)
 *
 * @author	Rene Nitzsche <dev@dmk-ebusiness.de>
 * @package	TYPO3
 */
class tx_t3users_mod_index extends Tx_Rnbase_Backend_AbstractFunctionModule {

	/**
	 * Returns the module menu
	 *
	 * @return	Array with menuitems
	 */
	function modMenu()	{
	  global $LANG;
		return Array (
//      "tx_lmo2cfcleague_modfunc1_check" => "",
		);
	}
	function init(&$pObj, $MCONF) {
		parent::init($pObj, $MCONF);
		$this->MCONF = $pObj->MCONF;
		$this->id = $pObj->id;
		$GLOBALS['LANG']->includeLLFile('EXT:t3users/mod/locallang.xml');
	}

	/**
	 * Main method of the module
	 *
	 * @return	HTML
	 */
	function main()	{
		// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS, $IMPORT_FUNC;
/*
Vorgehen
--------
*/
		$this->doc = $this->pObj->doc;
		$this->doc->tableLayout = tx_rnbase_mod_Tables::getTableLayout();
		$this->formTool = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_ToolBox');
		$this->formTool->init($this->doc, $this);

		$content .= tx_t3users_util_LoginAsFEUser::hijackUser();

		$module = tx_rnbase::makeInstance('tx_t3users_mod_mUserList', $this);
		$content .= $module->handleRequest($this->pObj->id);
		return $content;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/class.tx_t3users_mod_index.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/class.tx_t3users_mod_index.php']);
}
?>

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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_t3users_util_LoginAsFEUser');




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
class tx_t3users_mod_index extends t3lib_extobjbase {

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
		$this->doc->tableLayout = $this->getTableLayout();
		$this->formTool = tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
		$this->formTool->init($this->doc);
//		$this->selector = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_selector');
//		$this->selector->init($this->doc, $this->MCONF);

		$content .= tx_t3users_util_LoginAsFEUser::hijackUser();

		$module = tx_rnbase::makeInstance('tx_t3users_mod_mUserList', $this);
		$content .= $module->handleRequest($this->pObj->id);
		return $content;
	}

	/**
	 * Liefert das Layout für die Infotabelle
	 *
	 * @return array
	 */
  function getTableLayout() {
		$layout = Array (
			'table' => Array('<table class="typo3-dblist" cellspacing="0" cellpadding="0" border="0">', '</table><br/>'),
			'0' => Array( // Format für 1. Zeile
					'tr'		=> Array('<tr class="c-headLineTable">','</tr>'),
					'defCol' => (tx_rnbase_util_TYPO3::isTYPO42OrHigher() ? Array('<td>','</td>') : Array('<td class="c-headLineTable" style="font-weight:bold; color:white;">','</td>'))  // Format für jede Spalte in der 1. Zeile
			),
			'defRow' => Array ( // Formate für alle Zeilen
				'0' => Array('<td valign="top" style="padding:2px 5px;">','</td>'), // Format für 1. Spalte in jeder Zeile
				'defCol' => Array('<td valign="top" style="padding:0 5px;">','</td>') // Format für jede Spalte in jeder Zeile
			),
			'defRowEven' => Array ( // Formate für alle Zeilen
				'tr'	   => Array('<tr class="db_list_alt">', '</tr>'),
				'defCol' => Array((tx_rnbase_util_TYPO3::isTYPO42OrHigher() ?'<td style="padding:2px 5px;">' : '<td class="db_list_alt">'),'</td>')
			)
		);
		return $layout;
  }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/class.tx_t3users_mod_index.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/class.tx_t3users_mod_index.php']);
}
?>

<?php
/**
 *
 *  Copyright notice
 *
 *  (c) 2011 René Nitzsche <dev@dmk-ebusiness.de>
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
$GLOBALS['LANG']->includeLLFile('EXT:t3users/mod/locallang.xml');
// This checks permissions and exits if the users has no permission for entry.
$GLOBALS['BE_USER']->modAccess($GLOBALS['MCONF'], 1);
tx_rnbase::load('tx_rnbase_mod_BaseModule');

/**
 * Backend Modul für t3users
 *
 * @author René Nitzsche
 * @package tx_t3users
 * @subpackage tx_t3users_mod
 */
class  tx_t3users_module1 extends tx_rnbase_mod_BaseModule {

	/**
	 * Method to get the extension key
	 *
	 * @return	string Extension key
	 */
	public function getExtensionKey() {
		return 't3users';
	}

	protected function getFormTag() {
		$modUrl = Tx_Rnbase_Backend_Utility::getModuleUrl(
			'web_txt3usersM1', array('id' => $this->getPid()), ''
		);
		return '<form action="' . $modUrl . '" method="POST" name="editform" id="editform">';
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/index.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/index.php']);
}

// Make instance:
$SOBE = tx_rnbase::makeInstance('tx_t3users_module1');
$SOBE->init();

// Include files?
foreach((array) $SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>

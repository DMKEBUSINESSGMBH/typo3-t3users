<?php
/**
 *
 *  Copyright notice
 *
 *  (c) 2011 René Nitzsche <nitzsche@das-medienkombinat.de>
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

unset($MCONF);
require_once('conf.php');
require_once($REQUIRE_PATH.'init.php');
require_once($REQUIRE_PATH.'template.php');

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

$LANG->includeLLFile('EXT:t3users/mod/locallang.xml');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

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
		return '<form action="index.php?id=' . $this->getPid() . '" method="POST" name="editform" id="editform">';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/mod/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/mod/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_t3users_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
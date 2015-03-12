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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_t3users_util_Decorator');



/**
 * Show user list.
 */
class tx_t3users_mod_mUserList {
	var $mod;
	public function tx_t3users_mod_mUserList(&$mod) {
		$this->mod = $mod;
		$this->doc = $mod->doc;
	}
	/**
	 * Ausführung des Requests
	 *
	 * @param int $currPage
	 * @return string
	 */
	public function handleRequest(&$currPage) {
		global $LANG;

		$options = array();
		if($GLOBALS['BE_USER']->isAdmin())
			$options['linker'][] = tx_rnbase::makeInstance('tx_t3users_mod_LoginLink');

		$searcher = $this->getUserSearcher($options);
		$content .= $this->doc->section($LANG->getLL('msg_search_feuser'),$searcher->getSearchForm(), 0, 1);
		$content .= $this->doc->spacer(5);
//		$content .= $this->handleShowMembers();
//		$content .= $this->handleShowMessenger();
		$content .= $this->doc->spacer(5);
		$content .= $searcher->getResultList();
		$content .= $this->doc->spacer(5);
		return $content;

		// Anzeige eines Suchformulars
		// Anzeige einer Liste von Usern
		return 'Page: ' . $currPage;
	}

	/**
	 * Get a match searcher
	 *
	 * @param array $options
	 * @return tx_t3users_mod_userSearcher
	 */
	private function getUserSearcher(&$options) {
		$searcher = tx_rnbase::makeInstance('tx_t3users_mod_userSearcher', $this->mod, $options);
		return $searcher;
	}

}

class tx_t3users_mod_LoginLink implements tx_t3users_util_Linker {
	/**
	 * Login as feuser
	 *
	 * @param tx_t3users_models_feuser $item
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param int $currentPid
	 * @param array $options
	 * @return string
	 */
	function makeLink($item, $formTool, $currentPid, $options) {
		$out = $formTool->createSubmit('hijack['.$item->uid.']', 'FE-Anmeldung');

		return $out;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/class.tx_t3users_mod_mUserList.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/class.tx_t3users_mod_mUserList.php']);
}


?>

<?php
/**
 * Backend Modul
 * @package tx_t3users
 * @subpackage tx_t3users_mod
 */

/**
 * alle benötigten Klassen einbinden etc.
 */
if (!defined ('TYPO3_MODE')) {
  die ('Access denied.');
}
if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModule('web', 'txt3usersM1', 'top', t3lib_extMgm::extPath($_EXTKEY) . 'mod/');

	/**
	 * Callcenter Panel
	 */
	t3lib_extMgm::insertModuleFunction('web_txt3usersM1','tx_t3users_mod_FeUser',
		t3lib_extMgm::extPath($_EXTKEY).'mod/class.tx_t3users_mod_FeUser.php',
		'LLL:EXT:t3users/mod/locallang.xml:tx_t3users_module_name'
	);

	// Einbindung einer PageTSConfig
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$_EXTKEY.'/mod/pageTSconfig.txt">');
}


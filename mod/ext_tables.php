<?php
/**
 * Backend Modul.
 */

/*
 * alle benötigten Klassen einbinden etc.
 */
if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}
if (TYPO3_MODE == 'BE') {
    tx_rnbase_util_Extensions::addModule('web', 'txt3usersM1', 'top', tx_rnbase_util_Extensions::extPath($_EXTKEY).'mod/');

    /*
     * Callcenter Panel
     */
    tx_rnbase_util_Extensions::insertModuleFunction(
        'web_txt3usersM1',
        'tx_t3users_mod_FeUser',
        tx_rnbase_util_Extensions::extPath($_EXTKEY).'mod/class.tx_t3users_mod_FeUser.php',
        'LLL:EXT:t3users/mod/locallang.xml:tx_t3users_module_name'
    );

    // Einbindung einer PageTSConfig
    tx_rnbase_util_Extensions::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$_EXTKEY.'/mod/pageTSconfig.txt">');
}

<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3users_hooks_processDatamap.php:tx_t3users_hooks_processDatamap';
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3users_hooks_getMainFields.php:tx_t3users_hooks_getMainFields';

// Anpassung tslib_feuserauth
$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['tslib/class.tslib_feuserauth.php'] = t3lib_extMgm::extPath($_EXTKEY).'xclasses/class.ux_tslib_feuserauth.php';

$tempPath = t3lib_extMgm::extPath('t3users');
require_once($tempPath.'services/ext_localconf.php');
?>

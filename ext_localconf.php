<?php
t3lib_div::debug(array(
'?<s'
),__METHOD__.' Line: '.__LINE__); // @TODO: remove me
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3users_hooks_processDatamap.php:tx_t3users_hooks_processDatamap';
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3users_hooks_getMainFields.php:tx_t3users_hooks_getMainFields';

// Anpassung tslib_feuserauth
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_feuserauth.php'] = t3lib_extMgm::extPath($_EXTKEY).'xclasses/class.ux_tslib_feuserauth.php';

$tempPath = t3lib_extMgm::extPath('t3users');
require_once($tempPath.'services/ext_localconf.php');

//damit wir salted passwords mit sr_feuser => 2.6.3 nutzen können
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_configurations');
if(
	tx_rnbase_configurations::getExtensionCfgValue('t3users','useSaltedPasswordsWithSrFeUser') &&
	t3lib_div::int_from_ver(t3lib_extMgm::getExtensionVersion('sr_feuser_register')) >= 2006003

)
	$TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'] = t3lib_extMgm::extPath($_EXTKEY, 'xclasses/class.ux_tx_srfeuserregister_data.php');
?>

<?php
defined('TYPO3_MODE') || die ('Access denied.');

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3users_hooks_processDatamap.php:tx_t3users_hooks_processDatamap';
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3users_hooks_getMainFields.php:tx_t3users_hooks_getMainFields';


require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_configurations');
if (!tx_rnbase_configurations::getExtensionCfgValue($_EXTKEY, 'disableUxFeUserAuth')) {
	// Anpassung tslib_feuserauth
	if (class_exists('ux_tslib_feuserauth')) {
		$reflector = new ReflectionClass("ux_tslib_feuserauth");
		$rPath = realpath($reflector->getFileName());
		$tPath =  realpath(t3lib_extMgm::extPath($_EXTKEY, '/xclasses/class.ux_tslib_feuserauth.php'));
		// notice werfen wenn bisherige XClass nicht die von t3users ist
		if (strpos($rPath, $tPath) === FALSE) {
			throw new LogicException(
				'There allready exists an ux_tslib_feuserauth XCLASS in the path ' .$rPath . ' !' .
				' Remove the other XCLASS or or the user record won\'t be filled with the beforelastlogin column'
			);
		}
		unset($reflector, $rPath, $tPath);
	}
	// register the xclass
	else {
		require_once t3lib_extMgm::extPath($_EXTKEY, 'xclasses/class.ux_tslib_feuserauth.php');
		tx_rnbase::load('tx_rnbase_util_TYPO3');
		if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication'] = array(
					'className' => 'ux_tslib_feuserauth'
			);
		} else {
			$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['tslib/class.tslib_feuserauth.php'] =
				t3lib_extMgm::extPath($_EXTKEY, 'xclasses/class.ux_tslib_feuserauth.php');
		}
	}
}

require_once t3lib_extMgm::extPath($_EXTKEY, 'services/ext_localconf.php');

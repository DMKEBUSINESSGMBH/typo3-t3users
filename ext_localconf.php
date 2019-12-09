<?php
defined('TYPO3_MODE') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'tx_t3users_hooks_processDatamap';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'tx_t3users_hooks_getMainFields';

tx_rnbase::load('tx_rnbase_configurations');
if (!tx_rnbase_configurations::getExtensionCfgValue($_EXTKEY, 'disableUxFeUserAuth')) {
    // Anpassung tslib_feuserauth
    // kann schon durch autoloading da sein aber auch eine andere Klasse sein
    // als die von t3users
    if (class_exists('ux_tslib_feuserauth')) {
        $reflector = new ReflectionClass('ux_tslib_feuserauth');
        $rPath = realpath($reflector->getFileName());
        $tPath =  realpath(tx_rnbase_util_Extensions::extPath($_EXTKEY, '/xclasses/class.ux_tslib_feuserauth.php'));
        // notice werfen wenn bisherige XClass nicht die von t3users ist
        if (strpos($rPath, $tPath) === false) {
            throw new LogicException(
                'There allready exists an ux_tslib_feuserauth XCLASS in the path ' .$rPath . ' !' .
                ' Remove the other XCLASS or or the user record won\'t be filled with the beforelastlogin column'
            );
        }
        unset($reflector, $rPath, $tPath);
    } else {
        require_once tx_rnbase_util_Extensions::extPath($_EXTKEY, 'xclasses/class.ux_tslib_feuserauth.php');
    }

    tx_rnbase::load('tx_rnbase_util_TYPO3');
    if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication'] = array(
                'className' => 'ux_tslib_feuserauth'
        );
    } else {
        $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['tslib/class.tslib_feuserauth.php'] =
            tx_rnbase_util_Extensions::extPath($_EXTKEY, 'xclasses/class.ux_tslib_feuserauth.php');
    }
}

require_once tx_rnbase_util_Extensions::extPath($_EXTKEY, 'services/ext_localconf.php');

// solange das Plugin USER_INT ist, müssen ein paar Parameter für den cHash ausgeschlossen werden
tx_rnbase::load('Tx_Rnbase_Utility_Cache');
Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash(array(
    't3users[NK_forgotpass]',
    'logintype'
));

// register wizzard
tx_rnbase::load('tx_rnbase_util_TYPO3');
if (tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
    Tx_Rnbase_Backend_Utility_Icons::getIconRegistry()->registerIcon(
        'ext-t3users-wizard-icon',
        'TYPO3\\CMS\Core\\Imaging\\IconProvider\\BitmapIconProvider',
        array('source' => 'EXT:t3users/ext_icon.gif')
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:t3users/Configuration/TSconfig/ContentElementWizard.txt">'
    );
}

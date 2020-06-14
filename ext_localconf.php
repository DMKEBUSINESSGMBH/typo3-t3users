<?php
defined('TYPO3_MODE') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'tx_t3users_hooks_processDatamap';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'tx_t3users_hooks_getMainFields';

$_EXTKEY = 't3users';

if (!\Sys25\RnBase\Configuration\Processor::getExtensionCfgValue($_EXTKEY, 'disableUxFeUserAuth')) {
    // Anpassung tslib_feuserauth
    // kann schon durch autoloading da sein aber auch eine andere Klasse sein
    // als die von t3users
    // FIXME: sollte ggf. mal angepasst werden...
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

    if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication'] = array(
                'className' => 'ux_tslib_feuserauth'
        );
    } else {
        $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['tslib/class.tslib_feuserauth.php'] =
            tx_rnbase_util_Extensions::extPath($_EXTKEY, 'xclasses/class.ux_tslib_feuserauth.php');
    }
}

// START Services
tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    't3users' /* sv type */,
    'tx_t3users_services_feuser' /* sv key */,
    [
        'title' => 'FE-User services', 'description' => 'Service functions for feuser handling', 'subtype' => 'feuser',
        'available' => true, 'priority' => 50, 'quality' => 50,
        'os' => '', 'exec' => '',
        'className' => 'tx_t3users_services_feuser',
    ]
);

tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    't3users' /* sv type */,
    'tx_t3users_services_registration' /* sv key */,
    [
        'title' => 'FE-User registration services', 'description' => 'Service functions for feuser registration handling', 'subtype' => 'registration',
        'available' => true, 'priority' => 50, 'quality' => 50,
        'os' => '', 'exec' => '',
        'className' => 'tx_t3users_services_feuser',
    ]
);

tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    't3users' /* sv type */,
    'tx_t3users_services_logging' /* sv key */,
    [
        'title' => 'FE-Logging services', 'description' => 'Service functions for feuser logging', 'subtype' => 'logging',
        'available' => true, 'priority' => 50, 'quality' => 50,
        'os' => '', 'exec' => '',
        'className' => 'tx_t3users_services_logging',
    ]
);

tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    'auth' /* sv type */,
    'tx_t3users_services_feuserauth' /* sv key */,
    [
        'title' => 'Find FE-User', 'description' => 'Service functions for feuser handling', 'subtype' => 'getUserFE',
        'available' => true, 'priority' => 51, 'quality' => 50,
        'os' => '', 'exec' => '',
        'className' => 'tx_t3users_services_feuserauth',
    ]
);

tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    't3users' /* sv type */,
    'tx_t3users_services_email' /* sv key */,
    [
        'title' => 'Email service', 'description' => 'Service functions for email handling', 'subtype' => 'email',
        'available' => true, 'priority' => 51, 'quality' => 50,
        'os' => '', 'exec' => '',
        'className' => 'tx_t3users_services_email',
    ]
);

tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    't3users' /* sv type */,
    'tx_t3users_services_LoginForm' /* sv key */,
    [
        'title' => 'Service to extend LoginForm', 'description' => 'Service functions for security handling in login form', 'subtype' => 'loginform',
        'available' => true, 'priority' => 51, 'quality' => 50,
        'os' => '', 'exec' => '',
        'className' => 'tx_t3users_services_LoginForm',
    ]
);

// END Services

// solange das Plugin USER_INT ist, müssen ein paar Parameter für den cHash ausgeschlossen werden
Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash(array(
    't3users[NK_forgotpass]',
    'logintype'
));

if (TYPO3_MODE === 'BE') {
    // register wizard
    Tx_Rnbase_Backend_Utility_Icons::getIconRegistry()->registerIcon(
        'ext-t3users-wizard-icon',
        'TYPO3\\CMS\Core\\Imaging\\IconProvider\\BitmapIconProvider',
        ['source' => 'EXT:t3users/ext_icon.gif']
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:t3users/Configuration/TSconfig/ContentElementWizard.txt">'
    );

}


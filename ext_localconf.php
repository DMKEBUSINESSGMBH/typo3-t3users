<?php

defined('TYPO3') || exit('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'tx_t3users_hooks_processDatamap';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'tx_t3users_hooks_getMainFields';

$_EXTKEY = 't3users';

if (!\Sys25\RnBase\Configuration\Processor::getExtensionCfgValue($_EXTKEY, 'disableUxFeUserAuth')) {
    if (!empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class] ?? [])) {
        throw new LogicException('There is already an overwrite in $GLOBALS[\'TYPO3_CONF_VARS\'][\'SYS\'][\'Objects\'][\''.\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class.'\'] ! Remove the other overwrite or disable the overwrite of t3users.');
    }
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class] = [
        'className' => 'ux_tslib_feuserauth',
    ];
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    $_EXTKEY,
    't3users' /* sv type */ ,
    'tx_t3users_services_feuser' /* sv key */ ,
    [
        'title' => 'FE-User services', 'description' => 'Service functions for feuser handling', 'subtype' => 'feuser',
        'available' => true, 'priority' => 50, 'quality' => 50,
        'os' => '', 'exec' => '',
        'className' => 'tx_t3users_services_feuser',
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    $_EXTKEY,
    't3users' /* sv type */ ,
    'tx_t3users_services_registration' /* sv key */ ,
    [
        'title' => 'FE-User registration services', 'description' => 'Service functions for feuser registration handling', 'subtype' => 'registration',
        'available' => true, 'priority' => 50, 'quality' => 50,
        'os' => '', 'exec' => '',
        'className' => 'tx_t3users_services_feuser',
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    $_EXTKEY,
    't3users' /* sv type */ ,
    'tx_t3users_services_logging' /* sv key */ ,
    [
        'title' => 'FE-Logging services', 'description' => 'Service functions for feuser logging', 'subtype' => 'logging',
        'available' => true, 'priority' => 50, 'quality' => 50,
        'os' => '', 'exec' => '',
        'className' => 'tx_t3users_services_logging',
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    $_EXTKEY,
    'auth' /* sv type */ ,
    'tx_t3users_services_feuserauth' /* sv key */ ,
    [
        'title' => 'Find FE-User', 'description' => 'Service functions for feuser handling', 'subtype' => 'getUserFE',
        'available' => true, 'priority' => 51, 'quality' => 50,
        'os' => '', 'exec' => '',
        'className' => 'tx_t3users_services_feuserauth',
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    $_EXTKEY,
    't3users' /* sv type */ ,
    'tx_t3users_services_email' /* sv key */ ,
    [
        'title' => 'Email service', 'description' => 'Service functions for email handling', 'subtype' => 'email',
        'available' => true, 'priority' => 51, 'quality' => 50,
        'os' => '', 'exec' => '',
        'className' => 'tx_t3users_services_email',
    ]
);

// solange das Plugin USER_INT ist, müssen ein paar Parameter für den cHash ausgeschlossen werden
\Sys25\RnBase\Utility\CHashUtility::addExcludedParametersForCacheHash([
    't3users[NK_forgotpass]',
    'logintype',
]);

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)->registerIcon(
    'ext-t3users-wizard-icon',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:t3users/Resources/Public/Icons/Extension.gif']
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:t3users/Configuration/TSconfig/ContentElementWizard.tsconfig">'
);

<?php

if (!defined('TYPO3')) {
    exit('Access denied.');
}

if (\Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('t3users', 'activateBeModule')) {
    \Sys25\RnBase\Utility\Extensions::registerModule(
        't3users',
        'web',
        'backend',
        'bottom',
        [],
        [
            'access' => 'user,group',
            'routeTarget' => 'tx_t3users_mod_Module',
            'icon' => 'EXT:t3users/Resources/Public/Icons/moduleicon.png',
            'labels' => 'LLL:EXT:t3users/mod/locallang_mod.xml',
        ]
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
        'web_T3usersBackend',
        'tx_t3users_mod_FeUser',
        '',
        'LLL:EXT:t3users/mod/locallang.xml:tx_t3users_module_name'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:t3users/Configuration/TSconfig/BackendModule.tsconfig">'
    );
}

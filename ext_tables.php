<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$enableRoles = false;

// Backend Modul einbinden
if (TYPO3_MODE == 'BE' && tx_rnbase_configurations::getExtensionCfgValue('t3users', 'activateBeModule')
) {
    // register web_T3usersBackend
    tx_rnbase::load('tx_t3users_mod_Module');
    tx_rnbase_util_Extensions::registerModule(
        't3users',
        'web',
        'backend',
        'bottom',
        array(
        ),
        array(
            'access' => 'user,group',
            'routeTarget' => 'tx_t3users_mod_Module',
            'icon' => 'EXT:t3users/mod/moduleicon.png',
            'labels' => 'LLL:EXT:t3users/mod/locallang_mod.xml',
        )
    );

    /**
     * Callcenter Panel
     */
    tx_rnbase_util_Extensions::insertModuleFunction(
        'web_T3usersBackend',
        'tx_t3users_mod_FeUser',
        tx_rnbase_util_Extensions::extPath('t3users', 'mod/class.tx_t3users_mod_FeUser.php'),
        'LLL:EXT:t3users/mod/locallang.xml:tx_t3users_module_name'
    );

    // Einbindung einer PageTSConfig
    tx_rnbase_util_Extensions::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:t3users/mod/pageTSconfig.txt">'
    );
}

// @todo what is this for? $enableRoles will never be true
if ($enableRoles) {
    $TCA['tx_t3users_roles'] = array(
        'ctrl' => array(
            'title'     => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_roles',
            'label'     => 'name',
            'tstamp'    => 'tstamp',
            'crdate'    => 'crdate',
            'cruser_id' => 'cruser_id',
            'default_sortby' => 'ORDER BY name',
            'delete' => 'deleted',
            'enablecolumns' => array(
            ),
            'dynamicConfigFile' => tx_rnbase_util_Extensions::extPath('t3users', 'tca.php'),
            'iconfile'          => 'EXT:t3users/icon_tx_t3users_tables.gif',
        ),
        'feInterface' => array(
            'fe_admin_fieldList' => 'name',
        )
    );

    $TCA['tx_t3users_rights'] = array(
        'ctrl' => array(
            'title'     => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_rights',
            'label' => 'sign',
            'readOnly' => 1,    // This should always be true, as it prevents the static data from being altered
            'adminOnly' => 1,
            'rootLevel' => 1,
            'is_static' => 1,
            'default_sortby' => 'ORDER BY sign',
            'dynamicConfigFile' => tx_rnbase_util_Extensions::extPath('t3users', 'tca.php'),
            'iconfile'          => 'EXT:t3users/icon_tx_t3users_tables.gif',
        ),
        'interface' => array(
            'showRecordFieldList' => 'sign'
        )
    );
}

if (!tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
    // TCA registration for 4.5
    $TCA['tx_t3users_log'] = require tx_rnbase_util_Extensions::extPath('t3users', 'Configuration/TCA/tx_t3users_log.php');
    require tx_rnbase_util_Extensions::extPath('t3users', 'Configuration/TCA/Overrides/fe_users.php');
}

////////////////////////////////
// Plugin anmelden
////////////////////////////////
// Einige Felder ausblenden
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_t3users_main'] = 'select_key';

// Das tt_content-Feld pi_flexform einblenden
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_t3users_main'] = 'pi_flexform';

tx_rnbase_util_Extensions::addPiFlexFormValue(
    'tx_t3users_main',
    'FILE:EXT:t3users/Configuration/Flexform/flexform_main.xml'
);
tx_rnbase_util_Extensions::addPlugin(
    array('LLL:EXT:t3users/locallang_db.php:plugin.t3users.label', 'tx_t3users_main'),
    'list_type',
    't3users'
);


tx_rnbase_util_Extensions::addStaticFile('t3users', 'static/ts/', 'FE User Management');

if (TYPO3_MODE == 'BE') {
    tx_rnbase::load('tx_rnbase_util_TYPO3');
    if (!tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
        tx_rnbase::load('tx_t3users_controllers_wizicon');
        tx_t3users_controllers_wizicon::addWizicon(
            'tx_t3users_controllers_wizicon',
            tx_rnbase_util_Extensions::extPath(
                't3users',
                'controllers/class.tx_t3users_controllers_wizicon.php'
            )
        );
    }

    ////////////////////////////////
    // Submodul anmelden
    ////////////////////////////////
    if (!tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
        tx_rnbase_util_Extensions::insertModuleFunction(
            'web_func',
            'tx_t3users_mod_index',
            tx_rnbase_util_Extensions::extPath('t3users', 'mod/class.tx_t3users_mod_index.php'),
            'LLL:EXT:t3users/mod/locallang.xml:tx_t3users_module_name'
        );
    }
}

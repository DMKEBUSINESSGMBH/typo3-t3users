<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


// Backend Modul einbinden
if (TYPO3_MODE == 'BE' && \Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('t3users', 'activateBeModule')
) {
    // register web_T3usersBackend
    tx_rnbase_util_Extensions::registerModule(
        't3users',
        'web',
        'backend',
        'bottom',
        [],
        [
            'access' => 'user,group',
            'routeTarget' => 'tx_t3users_mod_Module',
            'icon' => 'EXT:t3users/mod/moduleicon.png',
            'labels' => 'LLL:EXT:t3users/mod/locallang_mod.xml',
        ]
    );

    /**
     * Callcenter Panel
     */
    tx_rnbase_util_Extensions::insertModuleFunction(
        'web_T3usersBackend',
        'tx_t3users_mod_FeUser',
        '',
        'LLL:EXT:t3users/mod/locallang.xml:tx_t3users_module_name'
    );

    // Einbindung einer PageTSConfig
    tx_rnbase_util_Extensions::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:t3users/mod/pageTSconfig.txt">'
    );
}

$enableRoles = false;
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


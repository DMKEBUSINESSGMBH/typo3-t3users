<?php

defined('TYPO3_MODE') or die();

call_user_func(function () {
    $extKey = 't3users';

    ////////////////////////////////
    // Plugin Competition anmelden
    ////////////////////////////////

    // Einige Felder ausblenden
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['tx_t3users_main'] = 'layout,select_key';

    // Das tt_content-Feld pi_flexform einblenden
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['tx_t3users_main'] = 'pi_flexform';

    tx_rnbase_util_Extensions::addPiFlexFormValue(
        'tx_t3users_main',
        'FILE:EXT:'.$extKey.'/Configuration/Flexform/flexform_main.xml'
    );

    tx_rnbase_util_Extensions::addPlugin(
        [
            'LLL:EXT:'.$extKey.'/locallang_db.php:plugin.t3users.label',
            'tx_t3users_main',
        ],
        'list_type',
        $extKey
    );
});
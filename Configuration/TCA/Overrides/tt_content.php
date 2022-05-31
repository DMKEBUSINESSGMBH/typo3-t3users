<?php

defined('TYPO3') or exit();

call_user_func(function () {
    $extKey = 't3users';

    // //////////////////////////////
    // Plugin Competition anmelden
    // //////////////////////////////

    // Einige Felder ausblenden
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['tx_t3users_main'] = 'layout,select_key';

    // Das tt_content-Feld pi_flexform einblenden
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['tx_t3users_main'] = 'pi_flexform';

    \Sys25\RnBase\Utility\Extensions::addPiFlexFormValue(
        'tx_t3users_main',
        'FILE:EXT:'.$extKey.'/Configuration/Flexform/flexform_main.xml'
    );

    \Sys25\RnBase\Utility\Extensions::addPlugin(
        [
            'LLL:EXT:'.$extKey.'/locallang_db.php:plugin.t3users.label',
            'tx_t3users_main',
        ],
        'list_type',
        $extKey
    );
});

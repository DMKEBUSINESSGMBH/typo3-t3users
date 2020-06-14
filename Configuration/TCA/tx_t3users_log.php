<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$tx_t3users_log = [
    'ctrl' => [
        'title'     => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log',
        'label'     => 'typ',
        'rootLevel' => 1,
        'default_sortby' => 'ORDER BY uid desc',
        'enablecolumns' => [],
        'iconfile'          => 'EXT:t3users/icon_tx_t3users_tables.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 'type'
    ],
    'feInterface' => [
        'fe_admin_fieldList' => 'name',
    ],
    'columns' => [
        'typ' => array(
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_typ',
            'config' => array(
                'type' => 'none',
            )
        ),
        'tstamp' => array(
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_tstamp',
            'config' => array(
                'type' => 'none',
            )
        ),
        'beuser' => array(
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_beuser',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'be_users',
                'size' => 1,
                'readOnly' => 1,
            )
        ),
        'feuser' => array(
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_feuser',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'size' => 1,
                'readOnly' => 1,
            )
        ),
        'recuid' => array(
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_recuid',
            'config' => array(
                'type' => 'none',
            )
        ),
        'rectable' => array(
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_rectable',
            'config' => array(
                'type' => 'none',
            )
        ),
        'data' => array(
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_data',
            'config' => array(
                'type' => 'none',
            )
        ),
    ],
    'types' => [
        '0' => ['showitem' => 'typ,tstamp,feuser,beuser,recuid,rectable,data']
    ],
    'palettes' => []
];

if (\Sys25\RnBase\Utility\TYPO3::isTYPO104OrHigher()) {
    unset($tx_t3users_log['interface']['showRecordFieldList']);
}

return $tx_t3users_log;

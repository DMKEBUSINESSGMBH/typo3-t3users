<?php

if (!defined('TYPO3')) {
    exit('Access denied.');
}

$tx_t3users_log = [
    'ctrl' => [
        'title' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:tx_t3users_log',
        'label' => 'typ',
        'rootLevel' => 1,
        'default_sortby' => 'ORDER BY uid desc',
        'enablecolumns' => [],
        'iconfile' => 'EXT:t3users/Resources/Public/Icons/icon_tx_t3users_tables.gif',
    ],
    'feInterface' => [
        'fe_admin_fieldList' => 'name',
    ],
    'columns' => [
        'typ' => [
            'label' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:tx_t3users_log_typ',
            'config' => [
                'type' => 'none',
            ],
        ],
        'tstamp' => [
            'label' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:tx_t3users_log_tstamp',
            'config' => [
                'type' => 'none',
            ],
        ],
        'beuser' => [
            'label' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:tx_t3users_log_beuser',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'be_users',
                'size' => 1,
                'readOnly' => 1,
            ],
        ],
        'feuser' => [
            'label' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:tx_t3users_log_feuser',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'size' => 1,
                'readOnly' => 1,
            ],
        ],
        'recuid' => [
            'label' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:tx_t3users_log_recuid',
            'config' => [
                'type' => 'none',
            ],
        ],
        'rectable' => [
            'label' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:tx_t3users_log_rectable',
            'config' => [
                'type' => 'none',
            ],
        ],
        'data' => [
            'label' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:tx_t3users_log_data',
            'config' => [
                'type' => 'none',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'typ,tstamp,feuser,beuser,recuid,rectable,data'],
    ],
    'palettes' => [],
];

return $tx_t3users_log;

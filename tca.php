<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

$TCA['tx_t3users_roles'] = [
    'ctrl' => $TCA['tx_t3users_roles']['ctrl'],
    'interface' => [
        'showRecordFieldList' => 'name,description',
    ],
    'feInterface' => $TCA['tx_t3users_roles']['feInterface'],
    'columns' => [
        'name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_roles.name',
            'config' => [
                'type' => 'input',
                'size' => '40',
                'max' => '150',
                'eval' => 'required',
            ],
        ],
        'description' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_roles.description',
            'config' => [
                'type' => 'text',
                'cols' => '40',
                'rows' => '10',
            ],
        ],

        'owner' => [ // LOCAL MM-Field
            'exclude' => 1,
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_roles.owner',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'feusers,fegroups',
                'size' => 10,
                'autoSizeMax' => 30,
                'minitems' => 0,
                'maxitems' => 100,
                'MM' => 'tx_t3users_role2owner_mm',
                'MM_match_fields' => [
                    'tablenames' => 'tx_t3users_role2owner_mm',
                ],
            ],
        ],

        'rights' => [ // FOREIGN MM-Field
            'exclude' => 1,
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_rights',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'allowed' => 'tx_t3users_rights',
                'size' => 10,
                'autoSizeMax' => 50,
                'minitems' => 0,
                'maxitems' => 100,
                'foreign_table' => 'tx_t3users_rights',
                'MM' => 'tx_t3users_right2role_mm',
                'MM_foreign_select' => 1,
                'MM_opposite_field' => 'roles',
                'MM_match_fields' => [
                    'tablenames' => 'tx_t3users_rights',
                ],
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, name, description,
            --div--;LLL:EXT:t3users/locallang_db.xml:tx_t3users_roles.description, rights, owner'],
    ],
    'palettes' => [
    ],
];

$TCA['tx_t3users_rights'] = [
    'ctrl' => $TCA['tx_t3users_rights']['ctrl'],
    'interface' => [
        'showRecordFieldList' => 'sign,description',
    ],
    'columns' => [
        'sign' => [
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_rights.sign',
            'exclude' => '0',
            'config' => [
                'type' => 'input',
                'size' => '7',
                'max' => '7',
                'eval' => 'int',
                'default' => '0',
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_roles.description',
            'exclude' => '0',
            'config' => [
                'type' => 'input',
                'size' => '7',
                'max' => '7',
                'eval' => 'int',
                'default' => '0',
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => 'sign,description',
        ],
    ],
];

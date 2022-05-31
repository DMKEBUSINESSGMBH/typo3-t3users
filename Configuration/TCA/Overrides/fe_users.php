<?php

$GLOBALS['TCA']['fe_users']['columns']['username']['config']['eval'] = 'nospace,uniqueInPid,required';

\Sys25\RnBase\Utility\Extensions::addTCAcolumns('fe_users', [
    // don't display in BE, but define it in TCA so that this column is included in fe_user-Model!
    'confirmstring' => [
        'type' => 'none',
    ],
    'confirmtimeout' => [
        'type' => 'none',
    ],
]);

if (intval(\Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('t3users', 'extendTCA'))) {
    $feUsersExtendedFields = [
        'gender' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:fe_users.gender',
            'config' => [
                'type' => 'radio',
                'items' => [
                    ['LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:fe_users_gender_mr', '0'],
                    ['LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:fe_users_gender_ms', '1'],
                ],
            ],
        ],
        'birthday' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:fe_users.birthday',
            'config' => [
                'type' => 'input',
                'size' => '12',
                'max' => '10',
                'default' => '00-00-0000',
            ],
        ],
    ];
    $feUsersExtendedFields = array_merge(
        $feUsersExtendedFields,
        [
            'first_name' => [
                'exclude' => 0,
                'label' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:fe_users.first_name',
                'config' => [
                    'type' => 'input',
                    'size' => '20',
                    'max' => '50',
                    'eval' => 'trim',
                    'default' => '',
                ],
            ],
            'last_name' => [
                'exclude' => 0,
                'label' => 'LLL:EXT:t3users/Resources/Private/Language/locallang_db.xlf:fe_users.last_name',
                'config' => [
                    'type' => 'input',
                    'size' => '20',
                    'max' => '50',
                    'eval' => 'trim',
                    'default' => '',
                ],
            ],
        ]
    );

    \Sys25\RnBase\Utility\Extensions::addTCAcolumns('fe_users', $feUsersExtendedFields);

    \Sys25\RnBase\Utility\Extensions::addToAllTCAtypes('fe_users', 'birthday', '', 'before:address');

    if (!\Sys25\RnBase\Utility\Extensions::isLoaded('sr_feuser_register')) {
        \Sys25\RnBase\Utility\Extensions::addToAllTCAtypes('fe_users', 'gender,title', '', 'before:birthday');
    }
}
if (intval(\Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('t3users', 'extendTCA'))) {
    \Sys25\RnBase\Utility\Extensions::addTCAcolumns('fe_users', [
        'lastlogin' => [
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.lastlogin',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'readOnly' => '1',
                'size' => '12',
                'eval' => 'datetime',
                'default' => 0,
            ],
        ],
    ]);
}

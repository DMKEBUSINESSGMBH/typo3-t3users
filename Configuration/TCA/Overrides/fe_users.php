<?php

tx_rnbase::load('tx_rnbase_util_TCA');
tx_rnbase_util_TCA::loadTCA('fe_users');
$GLOBALS['TCA']['fe_users']['columns']['username']['config']['eval'] = 'nospace,uniqueInPid,required';

if ($enableRoles) {
    tx_rnbase_util_Extensions::addTCAcolumns('fe_users', array(
        't3usersroles' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:t3users/locallang_db.xml:fe_users_t3usersroles',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'allowed' => 'tx_t3users_roles',
                'size' => 10,
                'autoSizeMax' => 50,
                'minitems' => 0,
                'maxitems' => 100,
                'foreign_table' => 'tx_t3users_roles',
                'MM' => 'tx_t3users_role2owner_mm',
                'MM_foreign_select' => 1,
                'MM_opposite_field' => 'owner',
                'MM_match_fields' => array(
                    'tablenames' => 'fe_users',
                ),
            )
        ),
    ));
    $GLOBALS['TCA']['fe_users']['types']['0']['showitem'] = str_replace(', starttime', ',t3usersroles, starttime', $GLOBALS['TCA']['fe_users']['types']['0']['showitem']);
}

tx_rnbase_util_Extensions::addTCAcolumns('fe_users', array(
    // don't display in BE, but define it in TCA so that this column is included in fe_user-Model!
    'confirmstring' => array(),
    'confirmtimeout' => array(),
));

/* If date2cal is loaded, include it as a wizard */
$date2CalTCA = array();
if (tx_rnbase_util_Extensions::isLoaded('date2cal')) {
    $date2CalTCA = array(
        'type' => 'userFunc',
        'userFunc' => 'EXT:date2cal/class.tx_date2cal_wizard.php:tx_date2cal_wizard->renderWizard',
        'evalValue' => 'date',
    );
    if (@is_dir(tx_rnbase_util_Extensions::extPath('date2cal').'/src')) {
        $date2CalTCA['userFunc'] = 'EXT:date2cal/src/class.tx_date2cal_wizard.php:tx_date2cal_wizard->renderWizard';
    }
}

if (intval(\Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('t3users', 'extendTCA'))) {
    $feUsersExtendedFields = array(
        'gender' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:t3users/locallang_db.xml:fe_users.gender',
            'config' => array(
                'type' => 'radio',
                'items' => array(
                    array('LLL:EXT:t3users/locallang_db.xml:fe_users_gender_mr', '0'),
                    array('LLL:EXT:t3users/locallang_db.xml:fe_users_gender_ms', '1'),
                ),
            )
        ),
        'birthday' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:t3users/locallang_db.xml:fe_users.birthday',
            'config' => array(
                'type' => 'input',
                'size' => '12',
                'max' => '10',
                'default' => '00-00-0000',
                'wizards' => array(
                    'calendar' => $date2CalTCA
                )
            )
        ),
    );
    // ab TYPO3 6.2 sind die Namensfelder direkt im Core enthalten
    if (!tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
        $feUsersExtendedFields = array_merge(
            $feUsersExtendedFields,
            array(
                'first_name' => array(
                    'exclude' => 0,
                    'label' => 'LLL:EXT:t3users/locallang_db.xml:fe_users.first_name',
                    'config' => array(
                        'type' => 'input',
                        'size' => '20',
                        'max' => '50',
                        'eval' => 'trim',
                        'default' => ''
                    )
                ),
                'last_name' => array(
                    'exclude' => 0,
                    'label' => 'LLL:EXT:t3users/locallang_db.xml:fe_users.last_name',
                    'config' => array(
                        'type' => 'input',
                        'size' => '20',
                        'max' => '50',
                        'eval' => 'trim',
                        'default' => ''
                    )
                )
            )
            );
    }

    tx_rnbase_util_Extensions::addTCAcolumns('fe_users', $feUsersExtendedFields);

    tx_rnbase_util_Extensions::addToAllTCAtypes('fe_users', 'birthday', '', 'before:address');

    if (!tx_rnbase_util_Extensions::isLoaded('sr_feuser_register')) {
        if (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
            tx_rnbase_util_Extensions::addToAllTCAtypes('fe_users', 'gender,title', '', 'before:birthday');
        } else {
            tx_rnbase_util_Extensions::addToAllTCAtypes('fe_users', 'first_name,last_name,gender,title', '', 'before:birthday');
        }
    }
}
if (intval(\Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('t3users', 'extendTCA'))) {
    tx_rnbase_util_Extensions::addTCAcolumns('fe_users', array(
        'lastlogin' => array(
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.lastlogin',
            'config' => array(
                'type' => 'input',
                'readOnly' => '1',
                'size' => '12',
                'eval' => 'datetime',
                'default' => 0,
            )
        ),
    ));
}

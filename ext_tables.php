<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$enableRoles = false;

// Backend Modul einbinden
if (
	TYPO3_MODE == 'BE' && tx_rnbase_configurations::getExtensionCfgValue('t3users', 'activateBeModule')
) {
	t3lib_extMgm::addModule('web', 'txt3usersM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod/');

	/**
	 * Callcenter Panel
	 */
	t3lib_extMgm::insertModuleFunction('web_txt3usersM1','tx_t3users_mod_FeUser',
		t3lib_extMgm::extPath($_EXTKEY).'mod/class.tx_t3users_mod_FeUser.php',
		'LLL:EXT:t3users/mod/locallang.xml:tx_t3users_module_name'
	);

	// Einbindung einer PageTSConfig
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$_EXTKEY.'/mod/pageTSconfig.txt">');
}

if($enableRoles) {
	$TCA['tx_t3users_roles'] = array (
		'ctrl' => array (
			'title'     => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_roles',
			'label'     => 'name',
			'tstamp'    => 'tstamp',
			'crdate'    => 'crdate',
			'cruser_id' => 'cruser_id',
	//		'type' => 'name',
	//		'dividers2tabs' => true,
			'default_sortby' => 'ORDER BY name',
			'delete' => 'deleted',
			'enablecolumns' => array (
			),
			'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
			'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_t3users_tables.gif',
		),
		'feInterface' => array (
			'fe_admin_fieldList' => 'name',
		)
	);

	$TCA['tx_t3users_rights'] = array(
		'ctrl' => array(
			'title'     => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_rights',
			'label' => 'sign',
			'readOnly' => 1,	// This should always be true, as it prevents the static data from being altered
			'adminOnly' => 1,
			'rootLevel' => 1,
			'is_static' => 1,
			'default_sortby' => 'ORDER BY sign',
			'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
			'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_t3users_tables.gif',
		),
		'interface' => array(
			'showRecordFieldList' => 'sign'
		)
	);
}

$TCA['tx_t3users_log'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log',
		'label'     => 'typ',
//		'tstamp'    => 'tstamp',
		'rootLevel' => 1,
//		'crdate'    => 'crdate',
//		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY uid desc',
//		'delete' => 'deleted',
		'enablecolumns' => array (
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'Configurations/tca/Log.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_t3users_tables.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'name',
	)
);



/* If date2cal is loaded, include it as a wizard */
$date2CalTCA = Array ();
if(t3lib_extMgm::isLoaded('date2cal')) {
	$date2CalTCA = Array (
		'type' => 'userFunc',
		'userFunc' => 'EXT:date2cal/class.tx_date2cal_wizard.php:tx_date2cal_wizard->renderWizard',
		'evalValue' => 'date',
	);
	if(@is_dir(t3lib_extMgm::extPath('date2cal').'/src')){
		$date2CalTCA['userFunc'] = 'EXT:date2cal/src/class.tx_date2cal_wizard.php:tx_date2cal_wizard->renderWizard';
	}
}


t3lib_div::loadTCA('fe_users');
$TCA['fe_users']['columns']['username']['config']['eval'] = 'nospace,uniqueInPid,required';

if($enableRoles) {
	t3lib_extMgm::addTCAcolumns('fe_users', Array(
			't3usersroles' => Array (
				'exclude' => 0,
				'label' => 'LLL:EXT:t3users/locallang_db.xml:fe_users_t3usersroles',
				'config' => Array (
					'type' => 'select',
					'allowed' => 'tx_t3users_roles',
					'size' => 10,
					'autoSizeMax' => 50,
					'minitems' => 0,
					'maxitems' => 100,
					'foreign_table' => 'tx_t3users_roles',
					'MM' => 'tx_t3users_role2owner_mm',
					'MM_foreign_select' => 1,
					'MM_opposite_field' => 'owner',
					'MM_match_fields' => Array (
						'tablenames' => 'fe_users',
					),
				)
			),
		)
	);
	$TCA['fe_users']['types']['0']['showitem'] = str_replace(', starttime', ',t3usersroles, starttime', $TCA['fe_users']['types']['0']['showitem']);
}
t3lib_extMgm::addTCAcolumns('fe_users', Array(
		// don't display in BE, but define it in TCA so that this column is included in fe_user-Model!
		'confirmstring' => Array (),
		'confirmtimeout' => Array (),
)
);


if (intval(tx_rnbase_configurations::getExtensionCfgValue('t3users','extendTCA'))) {
	$feUsersExtendedFields = array(
		'gender' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3users/locallang_db.xml:fe_users.gender',
			'config' => Array (
				'type' => 'radio',
				'items' => Array (
					Array('LLL:EXT:t3users/locallang_db.xml:fe_users_gender_mr', '0'),
					Array('LLL:EXT:t3users/locallang_db.xml:fe_users_gender_ms', '1'),
				),
			)
		),
		'birthday' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3users/locallang_db.xml:fe_users.birthday',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '10',
				'default' => '00-00-0000',
				'wizards' => Array (
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
				'first_name' => Array (
					'exclude' => 0,
					'label' => 'LLL:EXT:t3users/locallang_db.xml:fe_users.first_name',
					'config' => Array (
						'type' => 'input',
						'size' => '20',
						'max' => '50',
						'eval' => 'trim',
						'default' => ''
					)
				),
				'last_name' => Array (
					'exclude' => 0,
					'label' => 'LLL:EXT:t3users/locallang_db.xml:fe_users.last_name',
					'config' => Array (
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

	t3lib_extMgm::addTCAcolumns('fe_users', $feUsersExtendedFields);

	t3lib_extMgm::addToAllTCAtypes('fe_users', 'birthday','','before:address');

	if(!t3lib_extMgm::isLoaded('sr_feuser_register')) {
		if (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
			t3lib_extMgm::addToAllTCAtypes('fe_users', 'gender,title','','before:birthday');
		}
		else {
			t3lib_extMgm::addToAllTCAtypes('fe_users', 'first_name,last_name,gender,title','','before:birthday');
		}
	}
}
if (intval(tx_rnbase_configurations::getExtensionCfgValue('t3users','extendTCA'))) {
	t3lib_extMgm::addTCAcolumns('fe_users', Array(
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
		)
	);
}


////////////////////////////////
// Plugin anmelden
////////////////////////////////
// Einige Felder ausblenden
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_t3users_main']='layout,select_key,pages';

// Das tt_content-Feld pi_flexform einblenden
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_t3users_main']='pi_flexform';

t3lib_extMgm::addPiFlexFormValue('tx_t3users_main','FILE:EXT:'.$_EXTKEY.'/flexform_main.xml');
t3lib_extMgm::addPlugin(Array('LLL:EXT:'.$_EXTKEY.'/locallang_db.php:plugin.t3users.label','tx_t3users_main'));


t3lib_extMgm::addStaticFile($_EXTKEY,'static/ts/', 'FE User Management');

if (TYPO3_MODE=="BE")	{
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_t3users_controllers_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'controllers/class.tx_t3users_controllers_wizicon.php';

	////////////////////////////////
	// Submodul anmelden
	////////////////////////////////
	t3lib_extMgm::insertModuleFunction(
		'web_func',
		'tx_t3users_mod_index',
		t3lib_extMgm::extPath($_EXTKEY).'mod/class.tx_t3users_mod_index.php',
		'LLL:EXT:t3users/mod/locallang.xml:tx_t3users_module_name'
	);
}


?>
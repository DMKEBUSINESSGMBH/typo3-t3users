<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_t3users_log'] = array (
	'ctrl' => $TCA['tx_t3users_log']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'type'
	),
	'feInterface' => $TCA['tx_t3users_log']['feInterface'],
	'columns' => array (
		'typ' => Array (
			'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_typ',
			'config' => Array (
				'type' => 'none',
			)
		),
		'tstamp' => Array (
			'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_tstamp',
			'config' => Array (
				'type' => 'none',
			)
		),
		'beuser' => Array (
			'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_beuser',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'be_users',
				'size' => 1,
				'readOnly' => 1,
			)
		),
		'feuser' => Array (
			'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_feuser',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'readOnly' => 1,
			)
		),
		'recuid' => Array (
			'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_recuid',
			'config' => Array (
				'type' => 'none',
			)
		),
		'rectable' => Array (
			'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_rectable',
			'config' => Array (
				'type' => 'none',
			)
		),
		'data' => Array (
			'label' => 'LLL:EXT:t3users/locallang_db.xml:tx_t3users_log_data',
			'config' => Array (
				'type' => 'none',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'typ,tstamp,feuser,beuser,recuid,rectable,data')
	),
	'palettes' => array (
//		'1' => array('showitem' => 'starttime, endtime, fe_group')
	)
);


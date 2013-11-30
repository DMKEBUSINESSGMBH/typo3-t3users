<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_t3users_util_ServiceRegistry');

t3lib_extMgm::addService($_EXTKEY,  't3users' /* sv type */,  'tx_t3users_services_feuser' /* sv key */,
  array(
    'title' => 'FE-User services', 'description' => 'Service functions for feuser handling', 'subtype' => 'feuser',
    'available' => TRUE, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => t3lib_extMgm::extPath($_EXTKEY).'services/class.tx_t3users_services_feuser.php',
    'className' => 'tx_t3users_services_feuser',
  )
);

t3lib_extMgm::addService($_EXTKEY,  't3users' /* sv type */,  'tx_t3users_services_registration' /* sv key */,
	array(
		'title' => 'FE-User registration services', 'description' => 'Service functions for feuser registration handling', 'subtype' => 'registration',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'services/class.tx_t3users_services_feuser.php',
		'className' => 'tx_t3users_services_feuser',
	)
);

t3lib_extMgm::addService($_EXTKEY,  't3users' /* sv type */,  'tx_t3users_services_logging' /* sv key */,
  array(
    'title' => 'FE-Logging services', 'description' => 'Service functions for feuser logging', 'subtype' => 'logging',
    'available' => TRUE, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => t3lib_extMgm::extPath($_EXTKEY).'services/class.tx_t3users_services_logging.php',
    'className' => 'tx_t3users_services_logging',
  )
);

t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_t3users_services_feuserauth' /* sv key */,
  array(
    'title' => 'Find FE-User', 'description' => 'Service functions for feuser handling', 'subtype' => 'getUserFE',
    'available' => TRUE, 'priority' => 51, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => t3lib_extMgm::extPath($_EXTKEY).'services/class.tx_t3users_services_feuserauth.php',
    'className' => 'tx_t3users_services_feuserauth',
  )
);

t3lib_extMgm::addService($_EXTKEY,  't3users' /* sv type */,  'tx_t3users_services_email' /* sv key */,
  array(
    'title' => 'Email service', 'description' => 'Service functions for email handling', 'subtype' => 'email',
    'available' => TRUE, 'priority' => 51, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => t3lib_extMgm::extPath($_EXTKEY).'services/class.tx_t3users_services_email.php',
    'className' => 'tx_t3users_services_email',
  )
);

t3lib_extMgm::addService($_EXTKEY,  't3users' /* sv type */,  'tx_t3users_services_LoginForm' /* sv key */,
		array(
				'title' => 'Service to extend LoginForm', 'description' => 'Service functions for security handling in login form', 'subtype' => 'loginform',
				'available' => TRUE, 'priority' => 51, 'quality' => 50,
				'os' => '', 'exec' => '',
				'classFile' => t3lib_extMgm::extPath($_EXTKEY).'services/class.tx_t3users_services_LoginForm.php',
				'className' => 'tx_t3users_services_LoginForm',
		)
);

//
//t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_kbssignon_auth' /* sv key */,
//    array(
//
//      'title' => 'FE MD5 authentication',
//      'description' => 'Performs the server side part of the challenge response authentication.',
//
//      'subtype' => 'authUserFE',
//
//      'available' => true,
//      'priority' => 90,
//      'quality' => 50,
//
//      'os' => '',
//      'exec' => '',
//
//      'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_kbmd5fepw_sv1.php',
//      'className' => 'tx_kbmd5fepw_sv1',
//    )
//  );

?>
<?php

//
// Extension Manager/Repository config file for ext "t3users".
//
// Auto generated 13-05-2010 20:27
//
// Manual updates:
// Only the data in the array - everything else is removed by next
// writing. "version" and "dependencies" must not be touched!
//
$EM_CONF[$_EXTKEY] = array(
    'title' => 'FE-User Management',
    'description' => 'Enhanced frontend user management for TYPO3. User registration, login and management in a single extension. (Most features still under development!) Requires PHP5!',
    'category' => 'plugin',
    'author' => 'Rene Nitzsche,Hannes Bochmann,Michael Wagner,Christian Riesche',
    'author_email' => 'dev@dmk-ebusiness.de',
    'shy' => '',
    'dependencies' => 'rn_base,mkforms,mkmailer',
    'version' => '3.0.18',
    'conflicts' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 1,
    'lockType' => '',
    'author_company' => 'DMK E-BUSINESS GmbH',
    'constraints' => array(
        'depends' => array(
            'rn_base' => '1.4.0-',
            'typo3' => '4.5.0-8.7.99',
        ),
        'conflicts' => array(),
        'suggests' => array(
            'date2cal' => '',
            'kb_md5fepw' => '',
            'mkmailer' => '3.0.0-',
            'mkforms' => '3.0.0-',
        )
    ),
    '_md5_values_when_last_written' => 'a:63:{s:9:"ChangeLog";s:4:"38a3";s:10:"README.txt";s:4:"3bc0";s:21:"ext_conf_template.txt";s:4:"9e08";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"b3c7";s:14:"ext_tables.php";s:4:"a355";s:14:"ext_tables.sql";s:4:"8c14";s:25:"ext_tables_static+adt.sql";s:4:"8d2e";s:17:"flexform_main.xml";s:4:"7b00";s:26:"icon_tx_t3users_tables.gif";s:4:"475a";s:13:"locallang.xml";s:4:"a1e0";s:16:"locallang_db.xml";s:4:"2a78";s:7:"tca.php";s:4:"2890";s:47:"actions/class.tx_t3users_actions_EditFeUser.php";s:4:"110c";s:48:"actions/class.tx_t3users_actions_ListFeUsers.php";s:4:"6f19";s:42:"actions/class.tx_t3users_actions_Login.php";s:4:"2276";s:47:"actions/class.tx_t3users_actions_ShowFeUser.php";s:4:"e1fc";s:53:"actions/class.tx_t3users_actions_ShowRegistration.php";s:4:"140e";s:60:"actions/class.tx_t3users_actions_ShowRegistrationConfirm.php";s:4:"a2f6";s:49:"controllers/class.tx_t3users_controllers_main.php";s:4:"1b7c";s:52:"controllers/class.tx_t3users_controllers_wizicon.php";s:4:"1aa6";s:14:"doc/manual.sxw";s:4:"a127";s:19:"doc/wizard_form.dat";s:4:"d0ae";s:20:"doc/wizard_form.html";s:4:"9766";s:47:"exceptions/class.tx_t3users_exceptions_User.php";s:4:"67a1";s:21:"forms/feuser_edit.xml";s:4:"4aac";s:17:"forms/rawtest.xml";s:4:"1cb9";s:22:"forms/registration.xml";s:4:"48f5";s:46:"hooks/class.tx_t3users_hooks_getMainFields.php";s:4:"43d6";s:47:"hooks/class.tx_t3users_hooks_processDatamap.php";s:4:"c6f3";s:34:"mod/class.tx_t3users_mod_index.php";s:4:"0784";s:38:"mod/class.tx_t3users_mod_mUserList.php";s:4:"b701";s:41:"mod/class.tx_t3users_mod_userSearcher.php";s:4:"3b37";s:17:"mod/locallang.xml";s:4:"c29c";s:42:"models/class.tx_t3users_models_fegroup.php";s:4:"d6f6";s:41:"models/class.tx_t3users_models_feuser.php";s:4:"3956";s:38:"models/class.tx_t3users_models_log.php";s:4:"80c1";s:42:"search/class.tx_t3users_search_builder.php";s:4:"e92c";s:41:"search/class.tx_t3users_search_feuser.php";s:4:"b500";s:38:"search/class.tx_t3users_search_log.php";s:4:"8216";s:44:"services/class.tx_t3users_services_email.php";s:4:"d937";s:45:"services/class.tx_t3users_services_feuser.php";s:4:"85d7";s:49:"services/class.tx_t3users_services_feuserauth.php";s:4:"8497";s:46:"services/class.tx_t3users_services_logging.php";s:4:"999a";s:26:"services/ext_localconf.php";s:4:"510d";s:23:"static/ts/constants.txt";s:4:"eae0";s:19:"static/ts/setup.txt";s:4:"850d";s:25:"templates/feuseredit.html";s:4:"1c72";s:25:"templates/feuserlist.html";s:4:"d9e4";s:23:"templates/loginbox.html";s:4:"0cdf";s:27:"templates/registration.html";s:4:"e485";s:40:"util/class.tx_t3users_util_Decorator.php";s:4:"a2f0";s:46:"util/class.tx_t3users_util_FEUserDecorator.php";s:4:"df3b";s:44:"util/class.tx_t3users_util_FeGroupMarker.php";s:4:"f29a";s:43:"util/class.tx_t3users_util_FeUserMarker.php";s:4:"8edb";s:44:"util/class.tx_t3users_util_LoginAsFEUser.php";s:4:"4082";s:46:"util/class.tx_t3users_util_ServiceRegistry.php";s:4:"66fd";s:43:"views/class.tx_t3users_views_EditFeUser.php";s:4:"dda1";s:44:"views/class.tx_t3users_views_ListFeUsers.php";s:4:"0123";s:38:"views/class.tx_t3users_views_Login.php";s:4:"81f3";s:43:"views/class.tx_t3users_views_ShowFeUser.php";s:4:"8ede";s:49:"views/class.tx_t3users_views_ShowRegistration.php";s:4:"2e65";s:56:"views/class.tx_t3users_views_ShowRegistrationConfirm.php";s:4:"27aa";}',
    'suggests' => array(),
);

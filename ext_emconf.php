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
$EM_CONF['t3users'] = [
    'title' => 'FE-User Management',
    'description' => 'Enhanced frontend user management for TYPO3. User registration, login and management in a single extension. (Most features still under development!) Requires PHP5!',
    'category' => 'plugin',
    'author' => 'Rene Nitzsche,Hannes Bochmann,Michael Wagner,Christian Riesche',
    'author_email' => 'dev@dmk-ebusiness.de',
    'shy' => '',
    'dependencies' => 'rn_base',
    'version' => '9.2.0',
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
    'constraints' => [
        'depends' => [
            'rn_base' => '1.15.0-',
            'typo3' => '10.4.34-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'mkmailer' => '11.0.0-',
            'mkforms' => '11.0.0-',
        ],
    ],
    'suggests' => [],
];

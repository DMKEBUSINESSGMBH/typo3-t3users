<?php
/**
 * 	@package tx_t3users
 *  @subpackage tx_t3users_tests_services
 *
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_t3users_tests_Util');

/**
 * Testfälle für tx_t3users_services_feuser
 *
 * @package tx_t3users
 * @subpackage tx_t3users_tests_services
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_t3users_tests_ext_localconf_testcase extends tx_phpunit_testcase {

	/**
	 *
	 * @var string
	 */
	protected $sExpectedXClassPath;

	/**
	 *
	 * @var string
	 */
	protected $sXClassPathConfig;

	public function setUp() {
		if(!t3lib_extMgm::isLoaded('sr_feuser_register'))
			$this->marktestSkipped('sr_feuser_register muss installiert sein!');

		global $TYPO3_LOADED_EXT,$TYPO3_CONF_VARS;
		$this->sExpectedXClassPath = PATH_site.$TYPO3_LOADED_EXT['t3users']['siteRelPath'].'xclasses/class.ux_tx_srfeuserregister_data.php';
		$this->sXClassPathConfig = $TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'];
	}

	public function tearDown() {
		tx_t3users_tests_Util::setExtConfVar('useSaltedPasswordsWithSrFeUser', 0);
		//backup zurück
		global $TYPO3_CONF_VARS;
		$TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'] = $this->sXClassPathConfig;
	}

	public function testXClassIsNotRegisteredIfSrFeUserIsNotInstalledAndExtensionConfigNotActive() {
		//unload sr_feuser_register
		global $TYPO3_LOADED_EXT,$TYPO3_CONF_VARS;
		if(isset($TYPO3_LOADED_EXT['sr_feuser_register']))
			unset($TYPO3_LOADED_EXT['sr_feuser_register']);
		//evtl. geladene xclasses löschen
		$TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'] = null;

		$_EXTKEY = 't3users';
		require(t3lib_extMgm::extPath('t3users', 'ext_localconf.php'));

		$this->assertEmpty($TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'],'XClass wurde doch registriert!');
	}

	public function testXClassIsNotRegisteredIfSrFeUserIsNotInstalledAndExtensionConfigActive() {
		//unload sr_feuser_register
		global $TYPO3_LOADED_EXT,$TYPO3_CONF_VARS;
		if(isset($TYPO3_LOADED_EXT['sr_feuser_register']))
			unset($TYPO3_LOADED_EXT['sr_feuser_register']);
		//evtl. geladene xclasses löschen
		$TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'] = null;

		tx_t3users_tests_Util::setExtConfVar('useSaltedPasswordsWithSrFeUser', 1);

		$_EXTKEY = 't3users';
		require(t3lib_extMgm::extPath('t3users', 'ext_localconf.php'));

		//xclass darf nicht da sein
		$this->assertNotEquals($this->sExpectedXClassPath,$TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'],'XClass wurde doch registriert!');
	}

	public function testXClassIsNotRegisteredIfSrFeUserIsInstalledAndExtensionConfigNotActive() {
		//load sr_feuser_register
		global $TYPO3_LOADED_EXT,$TYPO3_CONF_VARS;
		$TYPO3_LOADED_EXT['sr_feuser_register'] = array();
		$TYPO3_LOADED_EXT['sr_feuser_register']['siteRelPath'] = 'typo3conf/ext/sr_feuser_register/';
		//evtl. geladene xclasses löschen
		$TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'] = null;

		if(!(t3lib_div::int_from_ver(t3lib_extMgm::getExtensionVersion('sr_feuser_register')) >= 2006003))
			$this->markTestSkipped('sr_feuser_register muss in Version 2.6.3 installiert sein damit dieser Test ausgeführt werden kann!');

		$_EXTKEY = 't3users';
		require(t3lib_extMgm::extPath('t3users', 'ext_localconf.php'));

		//xclass darf nicht da sein
		$this->assertNotEquals($this->sExpectedXClassPath,$TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'],'XClass wurde doch registriert!');
	}

	public function testXClassIsNotRegisteredIfSrFeUserIsInstalledInWrongVersionAndExtensionConfigActive() {
		//load sr_feuser_register
		global $TYPO3_LOADED_EXT,$TYPO3_CONF_VARS;
		$key = 'sr_feuser_register';
		$TYPO3_LOADED_EXT[$key] = array();
		$TYPO3_LOADED_EXT[$key]['siteRelPath'] = 'typo3conf/ext/sr_feuser_register/';
		//evtl. geladene xclasses löschen
		$TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'] = null;

		if(!(t3lib_div::int_from_ver(t3lib_extMgm::getExtensionVersion('sr_feuser_register')) < 2006003))
			$this->markTestSkipped('sr_feuser_register muss niedriger als in Version 2.6.3 installiert sein damit dieser Test ausgeführt werden kann!');

		tx_t3users_tests_Util::setExtConfVar('useSaltedPasswordsWithSrFeUser', 1);

		$_EXTKEY = 't3users';
		require(t3lib_extMgm::extPath('t3users', 'ext_localconf.php'));

		//xclass darf nicht da sein
		$this->assertNotEquals($this->sExpectedXClassPath,$TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'],'XClass wurde doch registriert!');
	}

	public function testXClassIsRegisteredIfSrFeUserIsInstalledInRightVersionAndExtensionConfigActive() {
		//load sr_feuser_register
		global $TYPO3_LOADED_EXT,$TYPO3_CONF_VARS;
		$key = 'sr_feuser_register';
		$TYPO3_LOADED_EXT[$key] = array();
		$TYPO3_LOADED_EXT[$key]['siteRelPath'] = 'typo3conf/ext/sr_feuser_register/';

		if(!(t3lib_div::int_from_ver(t3lib_extMgm::getExtensionVersion('sr_feuser_register')) >= 2006003))
			$this->markTestSkipped('sr_feuser_register muss in Version 2.6.3 installiert sein damit dieser Test ausgeführt werden kann!');

		//es darf keine andere xlcass geladen sein sonst unsere nicht genutzt wird!!!
		//eine art sicherheitscheck
		$this->assertEmpty($TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'],'Es ist bereits eine XClass registriert für "$TYPO3_CONF_VARS[\'FE\'][\'XCLASS\'][\'ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php\']" registriert. ('.$TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'].') D.h. unsere wird nicht geladen!!! Es könnte z.B. srfeuserregister_t3secsaltedpw geladen sein welche sr_feuser_register >= 2.6.3 nicht geht!');

		tx_t3users_tests_Util::setExtConfVar('useSaltedPasswordsWithSrFeUser', 1);

		$_EXTKEY = 't3users';
		require(t3lib_extMgm::extPath('t3users', 'ext_localconf.php'));

		$this->assertEquals($this->sExpectedXClassPath,$TYPO3_CONF_VARS['FE']['XCLASS']['ext/sr_feuser_register/model/class.tx_srfeuserregister_data.php'],'XClass wurde nicht registriert!');
	}
}
?>
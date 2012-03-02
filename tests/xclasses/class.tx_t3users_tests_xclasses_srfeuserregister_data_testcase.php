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
class tx_t3users_tests_xclasses_srfeuserregister_data_testcase extends tx_phpunit_testcase {

	protected $oXclass;

	public function setUp() {
		if(!t3lib_extMgm::isLoaded('sr_feuser_register'))
			$this->marktestSkipped('sr_feuser_register muss installiert sein!');

		require_once(t3lib_extMgm::extPath('sr_feuser_register') . 'model/class.tx_srfeuserregister_data.php');
		require_once(t3lib_extMgm::extPath('t3users') . 'xclasses/class.ux_tx_srfeuserregister_data.php');
		require_once(t3lib_extMgm::extPath('sr_feuser_register') . 'lib/class.tx_srfeuserregister_lib_tables.php');

		$this->oXclass = tx_rnbase::makeInstance('ux_tx_srfeuserregister_data');
	}

	public function testParseIncomingDataDoesNotTouchPasswordIfSaltedpasswordsIsNotInstalled() {
		//unload
		global $TYPO3_LOADED_EXT;
		if(isset($TYPO3_LOADED_EXT['saltedpasswords']))
			unset($TYPO3_LOADED_EXT['saltedpasswords']);

		$origArray = array('username'=>'johndoe','password'=>'mys3cr3t');
		$aParsed = $this->oXclass->parseIncomingData($origArray);

		$this->assertEquals('johndoe',$aParsed['username'],'username falsch');
		$this->assertEquals('mys3cr3t',$aParsed['password'],'password falsch');
	}

	public function testParseIncomingDataDoesNotTouchPasswordIfSaltedpasswordsIsInstalledButSecurityLevelNotAsExpected() {
		//laden
		global $TYPO3_LOADED_EXT;
		if(!isset($TYPO3_LOADED_EXT['saltedpasswords']))
			$TYPO3_LOADED_EXT['saltedpasswords'] = 1;

		//security level leeren damit tx_saltedpasswords_div::isUsageEnabled() false ist
		$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['loginSecurityLevel'] = '';

		$origArray = array('username'=>'johndoe','password'=>'mys3cr3t');
		$aParsed = $this->oXclass->parseIncomingData($origArray);

		$this->assertEquals('johndoe',$aParsed['username'],'username falsch');
		$this->assertEquals('mys3cr3t',$aParsed['password'],'password falsch');
	}

	public function testParseIncomingDataUnsetsPasswordIfSaltedpasswordsIsInstalledAndSecurityLevelAsExpected() {
		//laden
		global $TYPO3_LOADED_EXT;
		if(!isset($TYPO3_LOADED_EXT['saltedpasswords']))
			$TYPO3_LOADED_EXT['saltedpasswords'] = 1;

		//security level setzen damit tx_saltedpasswords_div::isUsageEnabled() true ist
		$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['loginSecurityLevel'] = 'rsa';
		tx_t3users_tests_Util::setExtConfVar(TYPO3_MODE.'.', array('enabled'=>1),'saltedpasswords');

		$origArray = array('username'=>'johndoe','password'=>'mys3cr3t');
		$aParsed = $this->oXclass->parseIncomingData($origArray);

		$this->assertEquals('johndoe',$aParsed['username'],'username falsch');
		$this->assertEquals('',$aParsed['password'],'password falsch');
	}

	public function testParseOutgoingDataDoesNotTouchPasswordIfSaltedpasswordsIsNotInstalled() {
		//unload
		global $TYPO3_LOADED_EXT;
		if(isset($TYPO3_LOADED_EXT['saltedpasswords']))
			unset($TYPO3_LOADED_EXT['saltedpasswords']);

		$dataArray = array('username'=>'johndoe','password'=>'mys3cr3t');
		$aParsed = $this->oXclass->parseOutgoingData(
			'dummyTable',
			'create',
			'1',
			array(),
			$dataArray,
			array()
		);

		$this->assertEquals('johndoe',$aParsed['username'],'username falsch');
		$this->assertEquals('mys3cr3t',$aParsed['password'],'password falsch');
	}

	public function testParseOutgoingDataDoesNotTouchPasswordIfSaltedpasswordsIsInstalledButSecurityLevelNotAsExpected() {
		//laden
		global $TYPO3_LOADED_EXT;
		if(!isset($TYPO3_LOADED_EXT['saltedpasswords']))
			$TYPO3_LOADED_EXT['saltedpasswords'] = 1;

		//security level leeren damit tx_saltedpasswords_div::isUsageEnabled() false ist
		$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['loginSecurityLevel'] = '';

		$dataArray = array('username'=>'johndoe','password'=>'mys3cr3t');
		$aParsed = $this->oXclass->parseOutgoingData(
			'dummyTable',
			'create',
			'1',
			array(),
			$dataArray,
			array()
		);

		$this->assertEquals('johndoe',$aParsed['username'],'username falsch');
		$this->assertEquals('mys3cr3t',$aParsed['password'],'password falsch');
	}

	public function testParseOutgoingDataUnsetsPasswordIfSaltedpasswordsIsInstalledAndSecurityLevelAsExpected() {
		//laden
		global $TYPO3_LOADED_EXT;
		if(!isset($TYPO3_LOADED_EXT['saltedpasswords']))
			$TYPO3_LOADED_EXT['saltedpasswords'] = 1;

		//security level setzen damit tx_saltedpasswords_div::isUsageEnabled() true ist
		$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['loginSecurityLevel'] = 'rsa';
		tx_t3users_tests_Util::setExtConfVar(TYPO3_MODE.'.', array('enabled'=>1),'saltedpasswords');

		$dataArray = array('username'=>'johndoe','password'=>'mys3cr3t');
		$aParsed = $this->oXclass->parseOutgoingData(
			'dummyTable',
			'create',
			'1',
			array(),
			$dataArray,
			array()
		);

		$this->assertEquals('johndoe',$aParsed['username'],'username falsch');
		$objPHPass = t3lib_div::makeInstance(tx_saltedpasswords_div::getDefaultSaltingHashingMethod());
		//passwort darf nicht das alte sein und sollte mit den ersten 4 zeichen
		//übereinstimmen für ein passwort was ebenfalls mit mit salted hash generiert wurde
		$this->assertNotEquals('mys3cr3t',$aParsed['password'],'password noch das alte');
		$this->assertEquals(substr($objPHPass->getHashedPassword('mys3cr3t'),0,4),substr($aParsed['password'],0,4),'password falsch salted');
	}
}
?>
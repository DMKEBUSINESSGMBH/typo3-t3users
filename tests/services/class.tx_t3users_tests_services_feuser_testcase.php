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

/**
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_t3users_util_ServiceRegistry');
tx_rnbase::load('tx_t3users_services_feuser');

/**
 * Testfälle für tx_t3users_services_feuser
 *
 * @package tx_t3users
 * @subpackage tx_t3users_tests_services
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_t3users_tests_services_feuser_testcase extends tx_phpunit_testcase {
	
	/**
	 * @group unit
	 * @dataProvider providerEmailDisable
	 */
	public function testEmailDisable($sEMail, $sResult) {
		$this->assertEquals(
				$sResult,
				tx_t3users_util_ServiceRegistry::getFeUserService()
					->emailDisable($sEMail)
			);
	}
	
	public function providerEmailDisable(){
		return array( //array($sEMail, $sResult),
				'Line: '.__LINE__ => array('ich@da.com', 'ich@@da.com'),
				'Line: '.__LINE__ => array('ich@@da.com', 'ich@@da.com'),
			);
	}
	/**
	 * @group unit
	 * @dataProvider providerEmailEnable
	 */
	public function testEmailEnable($sEMail, $sResult) {
		$this->assertEquals(
				$sResult,
				tx_t3users_util_ServiceRegistry::getFeUserService()
					->emailEnable($sEMail)
			);
	}
	
	public function providerEmailEnable() {
		return array( //array($sEMail, $sResult),
				'Line: '.__LINE__ => array('ich@da.com', 'ich@da.com'),
				'Line: '.__LINE__ => array('ich@@da.com', 'ich@da.com'),
				'Line: '.__LINE__ => array('ich@@@@@@da.com', 'ich@da.com'),
			);
	}
	
	/**
	 * @group unit
	 */
	public function testGetFeGroupsCallsNotDoSelectAndReturnsEmptyArrayIfUserHasNoGroups() {
		$feUserService = $this->getMock(
			'tx_t3users_services_feuser', array('getRnBaseDbUtil')
		);
		
		$feUserService->expects(($this->never()))
			->method('getRnBaseDbUtil');
			
		$feUserRecord = array('uid' => 1);
		$feUser = tx_rnbase::makeInstance('tx_t3users_models_feuser', $feUserRecord);
		$groups = $feUserService->getFeGroups($feUser);
		
		$this->assertTrue(is_array($groups), 'kein array zurück gegeben');
		$this->assertEmpty($groups, 'array nicht leer');
	}
	
	/**
	 * @group unit
	 */
	public function testGetFeGroupsCallsDoSelectAndReturnsCorrectArrayIfUserHasGroups() {
		$feUserService = $this->getMock(
			'tx_t3users_services_feuser', array('getRnBaseDbUtil')
		);
		
		$rnBaseDbUtil = $this->getMockClass(
			'tx_rnbase_util_DB', array('doSelect')
		);
		$usergroups = '1,2,3';
		$expectedOptions = array(
			'where' => 'uid IN (' . $usergroups . ') ',
			'wrapperclass' => 'tx_t3users_models_fegroup',
			'orderby' => 'title'
		);
		$rnBaseDbUtil::staticExpects($this->once())
			->method('doSelect')
			->with('*', 'fe_groups')
			->will($this->returnValue(array('testResult')));
			
		$feUserService->expects(($this->once()))
			->method('getRnBaseDbUtil')
			->will($this->returnValue($rnBaseDbUtil));
			
		$feUserRecord = array('uid' => 1, 'usergroup' => $usergroups);
		$feUser = tx_rnbase::makeInstance('tx_t3users_models_feuser', $feUserRecord);
		$groups = $feUserService->getFeGroups($feUser);
		
		$this->assertEquals(array('testResult'), $groups, 'gruppen falsch');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/tests/services/class.tx_t3users_tests_services_feuser_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/tests/services/class.tx_t3users_tests_services_feuser_testcase.php']);
}

?>
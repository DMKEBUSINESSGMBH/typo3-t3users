<?php
/**
 * 	@package tx_t3users
 *  @subpackage tx_t3users_tests_services
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH
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
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * Testfälle für tx_t3users_services_feuser
 *
 * @package tx_t3users
 * @subpackage tx_t3users_tests_services
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 */
class tx_t3users_tests_services_feuser_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		if (isset($_POST['user'])) {
			unset($_POST['user']);
		}
		if (isset($_POST['pass'])) {
			unset($_POST['pass']);
		}
		if (isset($_POST['logintype'])) {
			unset($_POST['logintype']);
		}
	}

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
	public function testGetRnBaseDbUtil() {
		self::assertInstanceOf(
			'Tx_Rnbase_Database_Connection',
			$this->callInaccessibleMethod(tx_rnbase::makeInstance('tx_t3users_services_feuser'), 'getRnBaseDbUtil')
		);
	}

	/**
	 * @group unit
	 */
	public function testGetFeGroupsCallsDoSelectAndReturnsCorrectArrayIfUserHasGroups() {
		$feUserService = $this->getMock(
			'tx_t3users_services_feuser', array('getRnBaseDbUtil')
		);

		$rnBaseDbUtil = $this->getMock(
			'tx_rnbase_util_DB', array('doSelect')
		);
		$usergroups = '1,2,3';
		$expectedOptions = array(
			'where' => 'uid IN (' . $usergroups . ') ',
			'wrapperclass' => 'tx_t3users_models_fegroup',
			'orderby' => 'title'
		);
		$rnBaseDbUtil->expects($this->once())
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

	/**
	 * @group unit
	 * @expectedException tx_t3users_exceptions_User
	 * @expectedExceptionMessage No user id given!
	 */
	public function testUpdateFeUserByConfirmstringThrowsExceptionIfNoUid() {
		$feUserService = $this->getMock(
			'tx_t3users_services_feuser', array('getRnBaseDbUtil')
		);
		$feUserService->expects(($this->never()))
			->method('getRnBaseDbUtil');

		$feUserService->updateFeUserByConfirmstring(0, '', array());
	}

	/**
	 * @group unit
	 * @expectedException tx_t3users_exceptions_User
	 * @expectedExceptionMessage No user id given!
	 */
	public function testUpdateFeUserByConfirmstringThrowsExceptionIfUidIsNoInteger() {
		$feUserService = $this->getMock(
			'tx_t3users_services_feuser', array('getRnBaseDbUtil')
		);
		$feUserService->expects(($this->never()))
			->method('getRnBaseDbUtil');

		$feUserService->updateFeUserByConfirmstring('abc', '', array());
	}

	/**
	 * @group unit
	 * @expectedException tx_t3users_exceptions_User
	 * @expectedExceptionMessage No confirmstring given!
	 */
	public function testUpdateFeUserByConfirmstringThrowsExceptionIfNoConfirmstring() {
		$feUserService = $this->getMock(
			'tx_t3users_services_feuser', array('getRnBaseDbUtil')
		);
		$feUserService->expects(self::never())
			->method('getRnBaseDbUtil');

		$feUserService->updateFeUserByConfirmstring(123, '', array());
	}

	/**
	 * @group unit
	 */
	public function testUpdateFeUserByConfirmstringCallsDoUpdateCorrectIfUidAndConfirmstring() {
		$feUserService = $this->getMock(
			'tx_t3users_services_feuser', array('getRnBaseDbUtil')
		);
		$databaseUtility = $this->getMock(
			'tx_rnbase_util_DB', array('doUpdate')
		);
		$data = array('city' => 'def');
		$databaseUtility->expects($this->once())
			->method('doUpdate')
			->with('fe_users', 'uid =	123 AND confirmstring = \'abc\'', $data, 0)
			->will($this->returnValue('updateResult'));

		$feUserService->expects(self::once())
			->method('getRnBaseDbUtil')
			->will(self::returnValue($databaseUtility));

		self::assertEquals(
			'updateResult',
			$feUserService->updateFeUserByConfirmstring(123, 'abc', $data)
		);
	}

	/**
	 * @group unit
	 */
	public function testGetOnlineUsersCallsSearchCorrect(){
		$feUserService = $this->getMock(
			'tx_t3users_services_feuser', array('search')
		);

		$expectedFields = array(
			'FESESSION.ses_userid' => array(OP_GT_INT => 0),
			'CUSTOM' => '(ses_tstamp+86400 > unix_timestamp() OR is_online+86400 > unix_timestamp())'
		);
		$expectedOptions = array(
			'pids' => '',
			'count' => 1,
			'distinct' => 1,
		);
		$feUserService->expects(self::once())
			->method('search')
			->with($expectedFields, $expectedOptions)
			->will($this->returnValue('searchResult'));

		self::assertEquals('searchResult', $feUserService->getOnlineUsers());
	}

	/**
	 * @group unit
	 */
	public function testGetOnlineUsersCallsSearchCorrectIfOptionsGiven(){
		$feUserService = $this->getMock(
			'tx_t3users_services_feuser', array('search')
		);

		$expectedFields = array(
			'FESESSION.ses_userid' => array(OP_GT_INT => 0),
			'CUSTOM' => '(ses_tstamp+86400 > unix_timestamp() OR is_online+86400 > unix_timestamp())',
			'FEUSER.pid' => array(OP_IN_INT => '1,2,3')
		);
		$expectedOptions = array(
			'pids' => '1,2,3',
			'distinct' => 1,
		);
		$feUserService->expects(self::once())
			->method('search')
			->with($expectedFields, $expectedOptions)
			->will($this->returnValue('searchResult'));

		self::assertEquals('searchResult', $feUserService->getOnlineUsers(array('pids' => '1,2,3')));
	}

	/**
	 * @group unit
	 */
	public function testLoginFrontendUserByUsernameAndPassword(){
		tx_rnbase_util_Misc::prepareTSFE(array('force'=>TRUE));

		$GLOBALS['TSFE']->fe_user = $this->getMock('stdClass', array('start'));
		$GLOBALS['TSFE']->fe_user->expects(self::once())->method('start');

		$feUserService = tx_t3users_util_ServiceRegistry::getFeUserService();
		$feUserService->loginFrontendUserByUsernameAndPassword('john@doe.com', 'S3cr3t');

		self::assertEquals('john@doe.com', $_POST['user'], 'Nutzername falsch in Postdata');
		self::assertEquals('S3cr3t', $_POST['pass'], 'Passwort falsch in Postdata');
		self::assertEquals('login', $_POST['logintype'], 'logintype falsch in Postdata');
	}
}

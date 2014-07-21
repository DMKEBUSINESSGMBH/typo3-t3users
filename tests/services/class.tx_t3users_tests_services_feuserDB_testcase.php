<?php
/**
 * 	@package tx_t3users
 *  @subpackage tx_t3users_tests_services
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_t3users_util_ServiceRegistry');
tx_rnbase::load('tx_t3users_tests_Util');


/**
 * Testfälle für tx_t3users_services_feuser
 *
 * @author hbochmann
 * @package tx_t3users
 * @subpackage tx_t3users_tests_services
 */
class tx_t3users_tests_services_feuserDB_testcase extends tx_phpunit_database_testcase {

	protected $feUserSrv;
	protected $workspaceIdAtStart;

	/**
	 * Klassenkonstruktor - BE-Workspace setzen
	 *
	 * @param unknown_type $name
	 */
	public function __construct ($name=null) {
		global $TYPO3_DB, $BE_USER;
		parent::__construct ($name);
		$TYPO3_DB->debugOutput = TRUE;

		$this->workspaceIdAtStart = $BE_USER->workspace;
		$BE_USER->setWorkspace(0);
	}

	/**
	 * setUp() = init DB etc.
	 */
	public function setUp(){
		// devlog deaktivieren
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['devlog']['nolog'] = true;

		$this->createDatabase();
		// assuming that test-database can be created otherwise PHPUnit will skip the test
		$this->useTestDatabase();
		$this->importStdDB();
		if(tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
			$this->importExtensions(array('frontend'));
		}
		$this->importExtensions(array('static_info_tables','t3users'),true);
		$fixturePath = tx_t3users_tests_Util::getFixturePath('db/feuser.xml');
		$this->importDataSet($fixturePath);

		$this->feUserSrv = tx_t3users_util_ServiceRegistry::getFeUserService();

	}

	/**
	 * tearDown() = destroy DB etc.
	 */
	public function tearDown () {
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);

		$GLOBALS['BE_USER']->setWorkspace($this->workspaceIdAtStart);
	}

	/**
	 *  @expectedException
	 */
	public function testUpdateFeUserByConfirmstringThrowsExceptionIfNoUserIdGiven(){
		try {
			$this->feUserSrv->updateFeUserByConfirmstring(null,'123072v2nzct2n',array());
		} catch (tx_t3users_exceptions_User $e) {
			$this->assertEquals('No user id given!',$e->getMessage(),'Es wurde nicht die korrekte Fehlermeldung zurück gegeben');
			return;
		}
		$this->fail('Es wurde nicht die erwartete tx_t3users_exceptions_User Exception geworfen!');
	}

	/**
	 *  @expectedException
	 */
	public function testUpdateFeUserByConfirmstringThrowsExceptionIfNoConfirmstringGiven(){
		try {
			$this->feUserSrv->updateFeUserByConfirmstring(1,null,array());
		} catch (tx_t3users_exceptions_User $e) {
			$this->assertEquals('No confirmstring given!',$e->getMessage(),'Es wurde nicht die korrekte Fehlermeldung zurück gegeben');
			return;
		}
		$this->fail('Es wurde nicht die erwartete tx_t3users_exceptions_User Exception geworfen!');
	}

	public function testGetOnlineUsersCount(){
		$this->addOnlineUserToDB();
		$onlineUsers = $this->feUserSrv->getOnlineUsers();
		$this->assertEquals(1, $onlineUsers);
	}

	public function testGetOnlineUserModels(){
		$this->addOnlineUserToDB();
		$onlineUsers = $this->feUserSrv->getOnlineUsers(array());
		$this->assertTrue(is_array($onlineUsers));
		$this->assertEquals(1, count($onlineUsers));
		/* @var $firstUser tx_t3users_models_feuser */
		$firstUser = array_shift($onlineUsers);
		$this->assertInstanceOf('tx_t3users_models_feuser', $firstUser);
		$this->assertEquals(1, $firstUser->isSessionActive());
	}

	protected function addOnlineUserToDB(){
		tx_rnbase_util_DB::doInsert('fe_users', array(
				'uid' => 100,
				'username' => 'test',
				'is_online' => ($GLOBALS['EXEC_TIME']-100),
		));
		tx_rnbase_util_DB::doInsert('fe_sessions', array(
				'ses_id' => 'ioe45jzh09w36',
				'ses_userid' => 100,
				'ses_tstamp' => ($GLOBALS['EXEC_TIME']-100),
		));
		tx_rnbase_util_DB::doInsert('fe_sessions', array(
				'ses_id' => 'aerj5tqa34z54',
				'ses_userid' => 100,
				'ses_tstamp' => ($GLOBALS['EXEC_TIME']-50),
		));
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/tests/services/class.tx_t3users_tests_services_feuserDB_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/tests/services/class.tx_t3users_tests_services_feuserDB_testcase.php']);
}

?>
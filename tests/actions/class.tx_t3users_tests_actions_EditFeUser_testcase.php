<?php
/**
 * 	@package tx_t3users
 *  @subpackage tx_t3users_tests_actions
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
tx_rnbase::load('tx_t3users_actions_EditFeUser');
tx_rnbase::load('tx_t3users_tests_Util');


/**
 * Testfälle für tx_t3users_actions_EditFeUser
 *
 * @author hbochmann
 * @package tx_t3users
 * @subpackage tx_t3users_tests_actions
 */
class tx_t3users_tests_actions_EditFeUser_testcase extends tx_phpunit_database_testcase {

  protected $actionsFeUser;
  protected $config;
  protected $parameters;
  protected $viewData;
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
		
    $this->actionsFeUser = tx_rnbase::makeInstance('tx_t3users_actions_EditFeUser');
    $this->config = tx_t3users_tests_Util::getConfigurations();
    $this->parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
    
    $this->createDatabase();
    // assuming that test-database can be created otherwise PHPUnit will skip the test
    $this->useTestDatabase();
    $this->importStdDB();
    $this->importExtensions(array('static_info_tables','t3users'),true);
    $fixturePath = tx_t3users_tests_Util::getFixturePath('db/feuser.xml');
    $this->importDataSet($fixturePath);
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
   * Pürfen on handleRequest() die Daten ändert und die richtige Meldung ausgibt wenn
   * der Modus "check" ist
   * @expectedException
   */
  public function testHandleRequestDoesntChangeDataIfNoUidGiven(){
  	$this->parameters->offsetSet('NK_email','dummy2@dummy2.de');
    $this->parameters->offsetSet('NK_confirmstring','123');
    $this->configData = array('feuseredit.' => array(
    				'mode' => 'check',
                  )
              );
    $this->cObj = t3lib_div::makeInstance('tslib_cObj');
    $this->config->init($this->configData, $this->cObj, 't3users', 't3users');
    $this->viewData = $this->config->getViewData();
    $this->actionsFeUser->handleRequest($this->parameters,$this->config,$this->viewData);
    
    $res = tx_rnbase_util_DB::doSelect('*', 'fe_users', array());
    $this->assertEquals(1,$res[0]['uid'],'[table:fe_users field:uid]] stimmt nicht!');
    $this->assertEquals('dummy1@dummy1.de',$res[0]['username'],'[table:fe_users field:username] stimmt nicht!');
    $this->assertEquals('dummy1@dummy1.de',$res[0]['email'],'[table:fe_users field:email] stimmt nicht!');
    $this->assertEquals('7346tbvn45tc91m9',$res[0]['confirmstring'],'[table:fe_users field:confirmstring] stimmt nicht!');
  }
  
	/**
   * Pürfen on handleRequest() die Daten ändert und die richtige Meldung ausgibt wenn
   * der Modus "check" ist
   * @expectedException
   */
  public function testHandleRequestDoesntChangeDataIfNoConfirmstringGiven(){
  	$this->parameters->offsetSet('NK_email','dummy2@dummy2.de');
  	$this->parameters->offsetSet('NK_uid',1);
    $this->configData = array('feuseredit.' => array(
    				'mode' => 'check',
                  )
              );
    $this->cObj = t3lib_div::makeInstance('tslib_cObj');
    $this->config->init($this->configData, $this->cObj, 't3users', 't3users');
    $this->viewData = $this->config->getViewData();
    $this->actionsFeUser->handleRequest($this->parameters,$this->config,$this->viewData);
    
    $res = tx_rnbase_util_DB::doSelect('*', 'fe_users', array());
    $this->assertEquals(1,$res[0]['uid'],'[table:fe_users field:uid]] stimmt nicht!');
    $this->assertEquals('dummy1@dummy1.de',$res[0]['username'],'[table:fe_users field:username] stimmt nicht!');
    $this->assertEquals('dummy1@dummy1.de',$res[0]['email'],'[table:fe_users field:email] stimmt nicht!');
    $this->assertEquals('7346tbvn45tc91m9',$res[0]['confirmstring'],'[table:fe_users field:confirmstring] stimmt nicht!');
  }

  /**
   * Pürfen on handleRequest() die Daten ändert und die richtige Meldung ausgibt wenn
   * der Modus "check" ist
   */
  public function testHandleRequestChangesDataIfUserValid(){
    $this->parameters->offsetSet('NK_uid',1);
    $this->parameters->offsetSet('NK_email','dummy2@dummy2.de');
    $this->parameters->offsetSet('NK_confirmstring','7346tbvn45tc91m9');
    
    $this->configData = array('feuseredit.' => array(
    				'mode' => 'check',
                  )
              );
    $this->cObj = t3lib_div::makeInstance('tslib_cObj');
    $this->config->init($this->configData, $this->cObj, 't3users', 't3users');
    $this->viewData = $this->config->getViewData();
    $this->actionsFeUser->handleRequest($this->parameters,$this->config,$this->viewData);
    
    $res = tx_rnbase_util_DB::doSelect('*', 'fe_users', array());

    $this->assertEquals(1,$res[0]['uid'],'[table:fe_users field:uid]] stimmt nicht!');
    $this->assertEquals('dummy2@dummy2.de',$res[0]['username'],'[table:fe_users field:username] stimmt nicht!');
    $this->assertEquals('dummy2@dummy2.de',$res[0]['email'],'[table:fe_users field:email] stimmt nicht!');
    $this->assertTrue(empty($res[0]['confirmstring']),'[table:fe_users field:confirmstring]] stimmt nicht!');
  }
  
  /**
   * Pürfen on handleRequest() die Daten ändert und die richtige Meldung ausgibt wenn
   * der Modus "check" ist
   */
  public function testHandleRequestDoesntChangeDataIfGivenDataInvalid(){
    $this->parameters->offsetSet('NK_uid',1);
    $this->parameters->offsetSet('NK_email','dummy2@dummy2.de');
    $this->parameters->offsetSet('NK_confirmstring','123');
    
    $this->configData = array('feuseredit.' => array(
    				'mode' => 'check',
                  )
              );
    $this->cObj = t3lib_div::makeInstance('tslib_cObj');
    $this->config->init($this->configData, $this->cObj, 't3users', 't3users');
    $this->viewData = $this->config->getViewData();
    $this->actionsFeUser->handleRequest($this->parameters,$this->config,$this->viewData);
    
    $res = tx_rnbase_util_DB::doSelect('*', 'fe_users', array());

    $this->assertEquals(1,$res[0]['uid'],'[table:fe_users field:uid]] stimmt nicht!');
    $this->assertEquals('dummy1@dummy1.de',$res[0]['username'],'[table:fe_users field:username] stimmt nicht!');
    $this->assertEquals('dummy1@dummy1.de',$res[0]['email'],'[table:fe_users field:email] stimmt nicht!');
    $this->assertEquals('7346tbvn45tc91m9',$res[0]['confirmstring'],'[table:fe_users field:confirmstring] stimmt nicht!');
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/tests/actions/class.tx_t3users_tests_actions_EditFeUser_testcase.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/tests/actions/class.tx_t3users_tests_actions_EditFeUser_testcase.php']);
}

?>
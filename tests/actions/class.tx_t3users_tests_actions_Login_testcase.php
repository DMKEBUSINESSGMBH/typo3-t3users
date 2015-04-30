<?php
/**
 * 	@package tx_t3users
 *  @subpackage tx_t3users_tests_actions
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <dev@dmk-ebusiness.de>
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
tx_rnbase::load('tx_t3users_actions_Login');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * tx_t3users_tests_actions_Login_testcase
 *
 * @package 		TYPO3
 * @subpackage	 	t3users
 * @author 			Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_t3users_tests_actions_Login_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		unset($_GET['redirect_url']);
	}

	/**
	 * @group unit
	 */
	public function testHandleNotLoggedInRemovesXssFromRedirectUrl() {
		$_GET['redirect_url'] =
			t3lib_div::getIndpEnv('TYPO3_SITE_URL') . "'><script>alert(\"ohoh\");</script>'";
		$loginAction = $this->getAccessibleMock(
			'tx_t3users_actions_Login',
			array(
				'prepareLoginFormOnSubmit', 'setLanguageMarkers',
				'getStoragePid', 'createPageUri'
			)
		);

		$parameters = $configurations = NULL;
		$viewData = new ArrayObject(array());
		$action = 'login';

		$loginAction->_callRef(
			'handleNotLoggedIn', $action, $parameters, $configurations, $viewData
		);

		$marker = $viewData->offsetGet('markers');

		$this->assertEquals(
			t3lib_div::getIndpEnv('TYPO3_SITE_URL') . '&#039;&gt;&lt;sc&lt;x&gt;ript&gt;alert(&quot;ohoh&quot;);&lt;/script&gt;&#039;',
			$marker['redirect_url']
		);
	}
}
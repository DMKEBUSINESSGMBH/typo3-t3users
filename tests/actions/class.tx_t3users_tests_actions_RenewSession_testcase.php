<?php
/**
 * 	@package TYPO3
 *  @subpackage tx_t3users
 *  @author Hannes Bochmann <dev@dmk-ebusiness.de>
 *
 *  Copyright notice
 *
 *  (c) 2012 Hannes Bochmann <dev@dmk-ebusiness.de>
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

tx_rnbase::load('tx_t3users_actions_RenewSession');

/**
 * Testfälle für tx_t3users_actions_RenewSession
 *
 * @package TYPO3
 * @subpackage tx_t3users
 * @author Hannes Bochmann <dev@dmk-ebusiness.de>
 */
class tx_t3users_tests_actions_RenewSession_testcase extends tx_phpunit_testcase {

	/**
	 *
	 */
	protected function tearDown() {
		if(isset($GLOBALS['TSFE']->additionalHeaderData['tx_t3users_actions_RenewSession']))
			unset($GLOBALS['TSFE']->additionalHeaderData['tx_t3users_actions_RenewSession']);
	}

	/**
	 * @group unit
	 */
	public function testHandleRequestReturnsCorrectJavaScriptWithDefaultIntervall() {
		$this->assertEmpty(
			$GLOBALS['TSFE']->additionalHeaderData['tx_t3users_actions_RenewSession'],
			'JavaScript bereits in Header Data gesetzt.'
		);

		$this->executeAction();

		$expectedJavaScript = $this->getExpectedJavaScriptWithIntervall();

		$this->assertEquals(
			$this->removeAnyWhitespace($expectedJavaScript),
			$this->removeAnyWhitespace($GLOBALS['TSFE']->additionalHeaderData['tx_t3users_actions_RenewSession']),
			'Das Javascript ist nicht wie erwatet'
		);
	}

	/**
	 * @group unit
	 */
	public function testHandleRequestReturnsCorrectJavaScriptWithIntervallSetByConfiguration() {
		$this->assertEmpty(
			$GLOBALS['TSFE']->additionalHeaderData['tx_t3users_actions_RenewSession'],
			'JavaScript bereits in Header Data gesetzt.'
		);

		$configurationsData = array(
			'renewSession.' => array(
    				'intervallInSeconds' => 400,
			)
		);
		$this->executeAction($configurationsData);

		$expectedJavaScript = $this->getExpectedJavaScriptWithIntervall(400000);

		$this->assertEquals(
			$this->removeAnyWhitespace($expectedJavaScript),
			$this->removeAnyWhitespace($GLOBALS['TSFE']->additionalHeaderData['tx_t3users_actions_RenewSession']),
			'Das Javascript ist nicht wie erwatet'
		);
	}

	/**
	 * @param array $configurationsData
	 *
	 * @return void
	 */
	private function executeAction(array $configurationsData = array()) {
		$action = tx_rnbase::makeInstance('tx_t3users_actions_RenewSession');
		$configurations = tx_t3users_tests_Util::getConfigurations();
		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');

		if(!empty($configurationsData)) {
			tx_rnbase::load('tx_rnbase_util_Typo3Classes');
			$cObj = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());
	    	$configurations->init($configurationsData, $cObj, 't3users', 't3users');
		}

		$action->execute($parameters,$configurations);
	}

	/**
	 * @param integer $intervall
	 *
	 * @return string
	 */
	private function getExpectedJavaScriptWithIntervall($intervall = 300000) {
		return
"<script type='text/javascript'>
	RenewSession = {
		loadCurrentPage: function(){
			var xmlhttp;

			// code for IE7+, Firefox, Chrome, Opera, Safari
			if (window.XMLHttpRequest) {
			  	xmlhttp=new XMLHttpRequest();
			// code for IE6, IE5
			} else {
			  	xmlhttp=new ActiveXObject('Microsoft.XMLHTTP');
			}
			xmlhttp.open('GET',window.location,true);
			xmlhttp.send();
		},

		loadCurrentPageInIntervall: function(intervall) {
			window.setInterval('RenewSession.loadCurrentPage()', intervall);
		}
	};

	RenewSession.loadCurrentPageInIntervall($intervall);
</script>";
	}

	/**
	 * @param string $text
	 * @return string
	 */
	private function removeAnyWhitespace($text) {
		return preg_replace('/\s+/', '', $text);
	}
}
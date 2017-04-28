<?php
/**
 * @package tx_t3users
 * @subpackage tx_t3users_tests_actions
 * @author Hannes Bochmann
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

tx_rnbase::load('tx_t3users_actions_Login');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * tx_t3users_tests_actions_Login_testcase
 *
 * @package         TYPO3
 * @subpackage      t3users
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_t3users_tests_actions_Login_testcase extends tx_rnbase_tests_BaseTestCase
{

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        /*
         * warning "Cannot modify header information" abfangen.
         *
         * Einige Tests lassen sich leider nicht ausführen:
         * "Cannot modify header information - headers already sent by"
         * Diese wird an unterschiedlichen stellen ausgelöst,
         * meißt jedoch bei Session operationen
         * Ab Typo3 6.1 laufend die Tests auch auf der CLI nicht.
         * Eigentlich gibt es dafür die runInSeparateProcess Anotation,
         * Allerdings funktioniert diese bei Typo3 nicht, wenn versucht wird
         * die GLOBALS in den anderen Prozess zu Übertragen.
         * Ein Deaktivierend er Übertragung führt dazu,
         * das Typo3 nicht initialisiert ist.
         *
         * Wir gehen also erst mal den Weg, den Fehler abzufangen.
         */
        set_error_handler(array(__CLASS__, 'errorHandler'), E_WARNING);
    }

    /**
     *
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
     * @param array $errcontext
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $ignoreMsg = array(
            'Cannot modify header information - headers already sent by',
        );
        foreach ($ignoreMsg as $msg) {
            if ((is_string($ignoreMsg) || is_numeric($ignoreMsg)) && strpos($errstr, $ignoreMsg) !== false) {
                // Don't execute PHP internal error handler
                return false;
            }
        }

        return null;
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($_GET['redirect_url']);

        // error handler zurücksetzen
        restore_error_handler();
    }

    /**
     * @group unit
     */
    public function testHandleNotLoggedInRemovesXssFromRedirectUrl()
    {
        $_GET['redirect_url'] =
            tx_rnbase_util_Misc::getIndpEnv('TYPO3_SITE_URL') . "'><script>alert(\"ohoh\");</script>'";
        $loginAction = $this->getAccessibleMock(
            'tx_t3users_actions_Login',
            array(
                'prepareLoginFormOnSubmit', 'setLanguageMarkers',
                'getStoragePid', 'createPageUri'
            )
        );

        $parameters = null;
        $configurations = $this->createConfigurations(array(), 't3users');
        $viewData = new ArrayObject(array());
        $action = 'login';
        $loginAction->setConfigurations($configurations);

        $loginAction->_callRef(
            'handleNotLoggedIn',
            $action,
            $parameters,
            $configurations,
            $viewData
        );

        $marker = $viewData->offsetGet('markers');

        $this->assertEquals(
            tx_rnbase_util_Misc::getIndpEnv('TYPO3_SITE_URL') . '&#039;&gt;&lt;sc&lt;x&gt;ript&gt;alert(&quot;ohoh&quot;);&lt;/script&gt;&#039;',
            $marker['redirect_url']
        );
    }

    /**
     * @group unit
     */
    public function testHandleNotLoggedInDelaysNotAfterFailedLoginsIfNotConfigured()
    {
        $loginAction = $this->getAccessibleMock(
            'tx_t3users_actions_Login',
            array(
                'prepareLoginFormOnSubmit', 'setLanguageMarkers',
                'getStoragePid', 'createPageUri'
            )
        );

        $parameters = null;
        $configurations = $this->createConfigurations(array(), 't3users');
        $viewData = new ArrayObject(array());
        $action = 'login';
        $loginAction->setConfigurations($configurations);

        $startTime = microtime(true);
        $loginAction->_callRef(
            'handleNotLoggedIn',
            $action,
            $parameters,
            $configurations,
            $viewData
        );
        self::assertLessThan(
            1,
            microtime(true) - $startTime,
            'mehr als 1 Sekunde vergangen. sleep scheint aufgerufen worden zu sein.'
        );
    }

    /**
     * @group unit
     */
    public function testHandleNotLoggedInDelaysAfterFailedLoginsIfConfigured()
    {
        $loginAction = $this->getAccessibleMock(
            'tx_t3users_actions_Login',
            array(
                'prepareLoginFormOnSubmit', 'setLanguageMarkers',
                'getStoragePid', 'createPageUri'
            )
        );

        $parameters = null;
        $configurations = $this->createConfigurations(array(
            'loginbox.' => array('delayInSecondsAfterFailedLogin' => 1)
        ), 't3users');
        $viewData = new ArrayObject(array());
        $action = 'login';
        $loginAction->setConfigurations($configurations);

        $startTime = microtime(true);
        $loginAction->_callRef(
            'handleNotLoggedIn',
            $action,
            $parameters,
            $configurations,
            $viewData
        );
        self::assertGreaterThan(
            1,
            microtime(true) - $startTime,
            'weniger als 1 Sekunde vergangen. sleep scheint nicht aufgerufen worden zu sein.'
        );
    }
}

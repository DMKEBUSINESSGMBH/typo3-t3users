<?php
/**
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

/**
 * tx_t3users_tests_actions_LoginTest.
 *
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_t3users_tests_actions_LoginTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    protected function setUp(): void
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
        set_error_handler([__CLASS__, 'errorHandler'], E_WARNING);
    }

    /**
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $ignoreMsg = [
            'Cannot modify header information - headers already sent by',
        ];
        foreach ($ignoreMsg as $msg) {
            if ((is_string($ignoreMsg) || is_numeric($ignoreMsg)) && false !== strpos($errstr, $ignoreMsg)) {
                // Don't execute PHP internal error handler
                return false;
            }
        }

        return null;
    }

    protected function tearDown(): void
    {
        unset($_GET['redirect_url']);

        // error handler zurücksetzen
        restore_error_handler();

        $property = new ReflectionProperty(\TYPO3\CMS\Core\Page\PageRenderer::class, 'jsInline');
        $property->setAccessible(true);
        $property->setValue(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class), []);
    }

    /**
     * @group unit
     */
    public function testHandleNotLoggedInRemovesXssFromRedirectUrl()
    {
        $_GET['redirect_url'] =
            \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL')."'><script>alert(\"ohoh\");</script>'";
        $loginAction = $this->getAccessibleMock(
            'tx_t3users_actions_Login',
            [
                'prepareLoginFormOnSubmit', 'setLanguageMarkers',
                'getStoragePid', 'createPageUri',
            ]
        );

        $configurations = \Sys25\RnBase\Testing\TestUtility::createConfigurations([], 't3users');
        $viewData = new ArrayObject([]);
        $action = 'login';

        $loginAction->_callRef(
            'handleNotLoggedIn',
            $action,
            $configurations,
            $viewData
        );

        $marker = $viewData->offsetGet('markers');

        $expectedSanitizedString = '&#039;&gt;&lt;script&gt;alert(&quot;ohoh&quot;);&lt;/script&gt;&#039;';

        $this->assertEquals(
            \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL').$expectedSanitizedString,
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
            [
                'prepareLoginFormOnSubmit', 'setLanguageMarkers',
                'getStoragePid', 'createPageUri',
            ]
        );

        $configurations = $this->createConfigurations([], 't3users');
        $viewData = new ArrayObject([]);
        $action = 'login';

        $startTime = microtime(true);
        $loginAction->_callRef(
            'handleNotLoggedIn',
            $action,
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
            [
                'prepareLoginFormOnSubmit', 'setLanguageMarkers',
                'getStoragePid', 'createPageUri',
            ]
        );

        $configurations = $this->createConfigurations([
            'loginbox.' => ['delayInSecondsAfterFailedLogin' => 1],
        ], 't3users');
        $viewData = new ArrayObject([]);
        $action = 'login';

        $startTime = microtime(true);
        $loginAction->_callRef(
            'handleNotLoggedIn',
            $action,
            $configurations,
            $viewData
        );
        self::assertGreaterThan(
            1,
            microtime(true) - $startTime,
            'weniger als 1 Sekunde vergangen. sleep scheint nicht aufgerufen worden zu sein.'
        );
    }

    /**
     * @group unit
     */
    public function testPrepareLoginFormOnSubmitAddsInlineJavaScriptCodeToFooterWithPageRenderer()
    {
        self::markTestIncomplete('GeneralUtility::devLog() will be removed with TYPO3 v10.0.');

        $loginAction = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_actions_Login');

        $configurations = $this->createConfigurations(
            ['loginbox.' => ['extend.' => ['method' => 'rsa7', 'rsa7.' => ['jsCode' => 'myCode']]]],
            't3users'
        );

        $markerArray = [];
        $this->callInaccessibleMethod(
            [$loginAction, 'prepareLoginFormOnSubmit'],
            [&$markerArray, 'whatever', $configurations, 'loginbox.']
        );

        $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        $property = new ReflectionProperty(get_class($pageRenderer), 'jsInline');
        $property->setAccessible(true);
        $inlineJavaScriptCode = $property->getValue($pageRenderer);
        self::assertArrayHasKey('t3users_loginBox', $inlineJavaScriptCode);
        self::assertEquals('myCode'.LF, $inlineJavaScriptCode['t3users_loginBox']['code']);
    }

    /**
     * @group unit
     */
    public function testPrepareLoginFormOnSubmitAddsInlineJavaScriptCodeNotToFooterIfNoneGiven()
    {
        self::markTestIncomplete('GeneralUtility::devLog() will be removed with TYPO3 v10.0.');

        $loginAction = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_actions_Login');

        $configurations = $this->createConfigurations(
            ['loginbox.' => ['extend.' => ['method' => 'rsa7']]],
            't3users'
            );

        $markerArray = [];
        $this->callInaccessibleMethod(
            [$loginAction, 'prepareLoginFormOnSubmit'],
            [&$markerArray, 'whatever', $configurations, 'loginbox.']
        );

        $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        $property = new ReflectionProperty(get_class($pageRenderer), 'jsInline');
        $property->setAccessible(true);
        $inlineJavaScriptCode = $property->getValue($pageRenderer);
        self::assertArrayNotHasKey('t3users_loginBox', $inlineJavaScriptCode);
    }
}

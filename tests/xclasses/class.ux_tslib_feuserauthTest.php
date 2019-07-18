<?php
/**
 * @package tx_t3users
 * @subpackage tx_t3users_tests_services
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
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * ux_tslib_feuserauthTest
 *
 * @package         TYPO3
 * @subpackage      t3users
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class ux_tslib_feuserauthTest extends tx_rnbase_tests_BaseTestCase
{

    /**
     * @var int
     */
    protected $sessionTimeoutBackup = 0;

    /**
     * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    protected $authentication;

    /**
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->sessionTimeoutBackup = $GLOBALS['TYPO3_CONF_VARS']['FE']['sessionTimeout'];
        $this->authentication = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getFrontendUserAuthenticationClass());
    }

    /**
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['sessionTimeout'] = $this->sessionTimeoutBackup;
        if ($this->authentication->id) {
            $this->authentication->logoff();
        }
    }

    /**
     * @group unit
     */
    public function testGetSessionTimeoutFieldByTypo3Version()
    {
        self::markTestIncomplete(
            "Call to undefined method TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::getSessionTimeoutFieldByTypo3Version()"
        );

        self::assertNotEmpty($this->authentication->getSessionTimeoutFieldByTypo3Version());
    }

    /**
     * @group unit
     * @todo can be removed when support for TYPO3 < 9 is dropped
     */
    public function testSessionTimeoutIsConfigurable()
    {
        if (tx_rnbase_util_TYPO3::isTYPO3VersionOrHigher(9000000)) {
            self::markTestSkipped('Only relevant before TYPO3 9.x');
        }

        $GLOBALS['TYPO3_CONF_VARS']['FE']['sessionTimeout'] = 123;
        $this->authentication->start();
        $sessionTimeoutField = $this->authentication->getSessionTimeoutFieldByTypo3Version();
        self::assertEquals(123, $this->authentication->$sessionTimeoutField);
    }
}

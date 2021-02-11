<?php

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * ux_tslib_feuserauthTest.
 *
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
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->sessionTimeoutBackup = $GLOBALS['TYPO3_CONF_VARS']['FE']['sessionTimeout'];
        $this->authentication = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getFrontendUserAuthenticationClass());
    }

    /**
     * {@inheritDoc}
     *
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
     *
     * @todo can be removed when support for TYPO3 < 9 is dropped
     */
    public function testSessionTimeoutIsConfigurable()
    {
        self::markTestIncomplete('RuntimeException: The requested database connection named "Default" has not been configured.');

        if (tx_rnbase_util_TYPO3::isTYPO3VersionOrHigher(9000000)) {
            self::markTestSkipped('Only relevant before TYPO3 9.x');
        }

        $GLOBALS['TYPO3_CONF_VARS']['FE']['sessionTimeout'] = 123;
        $this->authentication->start();
        $sessionTimeoutField = $this->authentication->getSessionTimeoutFieldByTypo3Version();
        self::assertEquals(123, $this->authentication->$sessionTimeoutField);
    }
}

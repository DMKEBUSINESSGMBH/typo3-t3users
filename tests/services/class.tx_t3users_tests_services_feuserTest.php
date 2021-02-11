<?php

tx_rnbase::load('tx_t3users_util_ServiceRegistry');
tx_rnbase::load('tx_t3users_services_feuser');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * Testfälle für tx_t3users_services_feuser.
 *
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 */
class tx_t3users_tests_services_feuserTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
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
    public function testEmailDisable($sEMail, $sResult)
    {
        self::markTestIncomplete('GeneralUtility::devLog() will be removed with TYPO3 v10.0.');

        $this->assertEquals(
            $sResult,
            tx_t3users_util_ServiceRegistry::getFeUserService()
                    ->emailDisable($sEMail)
        );
    }

    public function providerEmailDisable()
    {
        return [ //array($sEMail, $sResult),
                'Line: '.__LINE__ => ['ich@da.com', 'ich@@da.com'],
                'Line: '.__LINE__ => ['ich@@da.com', 'ich@@da.com'],
            ];
    }

    /**
     * @group unit
     * @dataProvider providerEmailEnable
     */
    public function testEmailEnable($sEMail, $sResult)
    {
        self::markTestIncomplete('GeneralUtility::devLog() will be removed with TYPO3 v10.0.');

        $this->assertEquals(
            $sResult,
            tx_t3users_util_ServiceRegistry::getFeUserService()
                    ->emailEnable($sEMail)
        );
    }

    public function providerEmailEnable()
    {
        return [ //array($sEMail, $sResult),
                'Line: '.__LINE__ => ['ich@da.com', 'ich@da.com'],
                'Line: '.__LINE__ => ['ich@@da.com', 'ich@da.com'],
                'Line: '.__LINE__ => ['ich@@@@@@da.com', 'ich@da.com'],
            ];
    }

    /**
     * @group unit
     */
    public function testGetFeGroupsCallsNotDoSelectAndReturnsEmptyArrayIfUserHasNoGroups()
    {
        $feUserService = $this->getMock(
            'tx_t3users_services_feuser',
            ['getRnBaseDbUtil']
        );

        $feUserService->expects(($this->never()))
            ->method('getRnBaseDbUtil');

        $feUserRecord = ['uid' => 1];
        $feUser = tx_rnbase::makeInstance('tx_t3users_models_feuser', $feUserRecord);
        $groups = $feUserService->getFeGroups($feUser);

        $this->assertTrue(is_array($groups), 'kein array zurück gegeben');
        $this->assertEmpty($groups, 'array nicht leer');
    }

    /**
     * @group unit
     */
    public function testGetRnBaseDbUtil()
    {
        self::assertInstanceOf(
            'Tx_Rnbase_Database_Connection',
            $this->callInaccessibleMethod(tx_rnbase::makeInstance('tx_t3users_services_feuser'), 'getRnBaseDbUtil')
        );
    }

    /**
     * @group unit
     */
    public function testGetFeGroupsCallsDoSelectAndReturnsCorrectArrayIfUserHasGroups()
    {
        $feUserService = $this->getMock(
            'tx_t3users_services_feuser',
            ['getRnBaseDbUtil']
        );

        $rnBaseDbUtil = $this->getMock(
            'tx_rnbase_util_DB',
            ['doSelect']
        );
        $usergroups = '1,2,3';
        $expectedOptions = [
            'where' => 'uid IN ('.$usergroups.') ',
            'wrapperclass' => 'tx_t3users_models_fegroup',
            'orderby' => 'title',
        ];
        $rnBaseDbUtil->expects($this->once())
            ->method('doSelect')
            ->with('*', 'fe_groups')
            ->will($this->returnValue(['testResult']));

        $feUserService->expects(($this->once()))
            ->method('getRnBaseDbUtil')
            ->will($this->returnValue($rnBaseDbUtil));

        $feUserRecord = ['uid' => 1, 'usergroup' => $usergroups];
        $feUser = tx_rnbase::makeInstance('tx_t3users_models_feuser', $feUserRecord);
        $groups = $feUserService->getFeGroups($feUser);

        $this->assertEquals(['testResult'], $groups, 'gruppen falsch');
    }

    /**
     * @group unit
     * @expectedException \tx_t3users_exceptions_User
     * @expectedExceptionMessage No user id given!
     */
    public function testUpdateFeUserByConfirmstringThrowsExceptionIfNoUid()
    {
        $feUserService = $this->getMock(
            'tx_t3users_services_feuser',
            ['getRnBaseDbUtil']
        );
        $feUserService->expects(($this->never()))
            ->method('getRnBaseDbUtil');

        $feUserService->updateFeUserByConfirmstring(0, '', []);
    }

    /**
     * @group unit
     * @expectedException \tx_t3users_exceptions_User
     * @expectedExceptionMessage No user id given!
     */
    public function testUpdateFeUserByConfirmstringThrowsExceptionIfUidIsNoInteger()
    {
        $feUserService = $this->getMock(
            'tx_t3users_services_feuser',
            ['getRnBaseDbUtil']
        );
        $feUserService->expects(($this->never()))
            ->method('getRnBaseDbUtil');

        $feUserService->updateFeUserByConfirmstring('abc', '', []);
    }

    /**
     * @group unit
     * @expectedException \tx_t3users_exceptions_User
     * @expectedExceptionMessage No confirmstring given!
     */
    public function testUpdateFeUserByConfirmstringThrowsExceptionIfNoConfirmstring()
    {
        $feUserService = $this->getMock(
            'tx_t3users_services_feuser',
            ['getRnBaseDbUtil']
        );
        $feUserService->expects(self::never())
            ->method('getRnBaseDbUtil');

        $feUserService->updateFeUserByConfirmstring(123, '', []);
    }

    /**
     * @group unit
     */
    public function testUpdateFeUserByConfirmstringCallsDoUpdateCorrectIfUidAndConfirmstring()
    {
        $feUserService = $this->getMock(
            'tx_t3users_services_feuser',
            ['getRnBaseDbUtil']
        );
        $databaseUtility = $this->getMock(
            'tx_rnbase_util_DB',
            ['doUpdate']
        );
        $data = ['city' => 'def'];
        $databaseUtility->expects($this->once())
            ->method('doUpdate')
            ->with('fe_users', 'uid = 123 AND confirmstring = \'abc\'', $data, 0)
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
    public function testGetOnlineUsersCallsSearchCorrect()
    {
        $feUserService = $this->getMock(
            'tx_t3users_services_feuser',
            ['search']
        );

        $expectedFields = [
            'FESESSION.ses_userid' => [OP_GT_INT => 0],
            'CUSTOM' => sprintf(
                '(ses_tstamp+%1$d > unix_timestamp() OR is_online+%1$d > unix_timestamp())',
                $feUserService->getSessionLifeTime()
            ),
        ];
        $expectedOptions = [
            'pids' => '',
            'count' => 1,
            'distinct' => 1,
        ];
        $feUserService->expects(self::once())
            ->method('search')
            ->with($expectedFields, $expectedOptions)
            ->will($this->returnValue('searchResult'));

        self::assertEquals('searchResult', $feUserService->getOnlineUsers());
    }

    /**
     * @group unit
     */
    public function testGetOnlineUsersCallsSearchCorrectIfOptionsGiven()
    {
        $feUserService = $this->getMock(
            'tx_t3users_services_feuser',
            ['search']
        );

        $expectedFields = [
            'FESESSION.ses_userid' => [OP_GT_INT => 0],
            'CUSTOM' => sprintf(
                '(ses_tstamp+%1$d > unix_timestamp() OR is_online+%1$d > unix_timestamp())',
                $feUserService->getSessionLifeTime()
            ),
            'FEUSER.pid' => [OP_IN_INT => '1,2,3'],
        ];
        $expectedOptions = [
            'pids' => '1,2,3',
            'distinct' => 1,
        ];
        $feUserService->expects(self::once())
            ->method('search')
            ->with($expectedFields, $expectedOptions)
            ->will($this->returnValue('searchResult'));

        self::assertEquals('searchResult', $feUserService->getOnlineUsers(['pids' => '1,2,3']));
    }

    /**
     * @group unit
     */
    public function testLoginFrontendUserByUsernameAndPassword()
    {
        self::markTestIncomplete("Error: Class 'TYPO3\CMS\Core\TimeTracker\NullTimeTracker' not found");

        tx_rnbase_util_Misc::prepareTSFE(['force' => true]);

        $GLOBALS['TSFE']->fe_user = $this->getMock('stdClass', ['start']);
        $GLOBALS['TSFE']->fe_user->expects(self::once())->method('start');

        $feUserService = tx_t3users_util_ServiceRegistry::getFeUserService();
        $feUserService->loginFrontendUserByUsernameAndPassword('john@doe.com', 'S3cr3t');

        self::assertEquals('john@doe.com', $_POST['user'], 'Nutzername falsch in Postdata');
        self::assertEquals('S3cr3t', $_POST['pass'], 'Passwort falsch in Postdata');
        self::assertEquals('login', $_POST['logintype'], 'logintype falsch in Postdata');
    }
}

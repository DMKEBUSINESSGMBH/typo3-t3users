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
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.    See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */
tx_rnbase::load('tx_t3users_actions_EditFeUser');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * Testfälle für tx_t3users_actions_EditFeUser.
 *
 * @author hbochmann
 */
class tx_t3users_tests_actions_EditFeUserTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $GLOBALS['LOCAL_LANG']['default']['msg_change_error'][0]['target'] = 'error on update';
        $GLOBALS['LOCAL_LANG']['default']['msg_change_success'][0]['target'] = 'success on update';
    }

    /**
     * @group unit
     */
    public function testGetFeUserService()
    {
        self::markTestIncomplete('GeneralUtility::devLog() will be removed with TYPO3 v10.0.');

        self::assertInstanceOf(
            'tx_t3users_services_feuser',
            $this->callInaccessibleMethod(
                tx_rnbase::makeInstance('tx_t3users_actions_EditFeUser'),
                'getFeUserService'
            )
        );
    }

    /**
     * @param int $uid
     * @param string $confirmString
     * @group unit
     * @dataProvider dataProviderUidAndConfirmstringParameter
     */
    public function testHandleRequestInModeCheckReturnsCorrectMessageIfMissingParameter(
        $uid,
        $confirmString
    ) {
        self::markTestIncomplete('Uncaught require(typo3-t3users/.Build/Web/typo3conf/LocalConfiguration.php');

        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $parameters->offsetSet('NK_uid', $uid);
        $parameters->offsetSet('NK_confirmstring', $confirmString);

        $errorMessage = $this->getActionMessageByParametersAndFeUserService($parameters);

        self::assertEquals('error on update', $errorMessage, 'Fehlermeldung falsch');
    }

    /**
     * @return multitype:multitype:number string
     */
    public function dataProviderUidAndConfirmstringParameter()
    {
        return [
            [0, ''],
            [0, '123'],
            [123, ''],
        ];
    }

    /**
     * @group unit
     */
    public function testHandleRequestInModeCheckCallsUpdateFeUserByConfirmstringCorrectIfConfirmstringAndUid()
    {
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $parameters->offsetSet('NK_uid', 123);
        $parameters->offsetSet('NK_confirmstring', 'abc');

        $expectedParameters = ['confirmstring' => ''];
        $feUserService = $this->getMock('tx_t3users_services_feuser', ['updateFeUserByConfirmstring']);
        $feUserService->expects(self::once())
            ->method('updateFeUserByConfirmstring')
            ->with(123, 'abc', $expectedParameters);

        $this->getActionMessageByParametersAndFeUserService($parameters, $feUserService);
    }

    /**
     * @group unit
     */
    public function testHandleRequestInModeCheckCallsUpdateFeUserByConfirmstringCorrectIfConfirmstringAndUidAndAdditionalNkFieldsGiven()
    {
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $parameters->offsetSet('NK_uid', 123);
        $parameters->offsetSet('NK_confirmstring', 'abc');
        $parameters->offsetSet('NK_city', 'def');

        $expectedParameters = ['confirmstring' => '', 'city' => 'def'];
        $feUserService = $this->getMock('tx_t3users_services_feuser', ['updateFeUserByConfirmstring']);
        $feUserService->expects(self::once())
            ->method('updateFeUserByConfirmstring')
            ->with(123, 'abc', $expectedParameters);

        $this->getActionMessageByParametersAndFeUserService($parameters, $feUserService);
    }

    /**
     * @group unit
     */
    public function testHandleRequestInModeCheckCallsUpdateFeUserByConfirmstringCorrectIfConfirmstringAndUidAndEmailParameter()
    {
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $parameters->offsetSet('NK_uid', 123);
        $parameters->offsetSet('NK_confirmstring', 'abc');
        $parameters->offsetSet('NK_email', 'def');

        $expectedParameters = ['confirmstring' => '', 'email' => 'def', 'username' => 'def'];
        $feUserService = $this->getMock('tx_t3users_services_feuser', ['updateFeUserByConfirmstring']);
        $feUserService->expects(self::once())
            ->method('updateFeUserByConfirmstring')
            ->with(123, 'abc', $expectedParameters);

        $this->getActionMessageByParametersAndFeUserService($parameters, $feUserService);
    }

    /**
     * @group unit
     */
    public function testHandleRequestInModeReturnsCorrectMessageIfUpdateSuccess()
    {
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $parameters->offsetSet('NK_uid', 123);
        $parameters->offsetSet('NK_confirmstring', 'abc');

        $feUserService = $this->getMock('tx_t3users_services_feuser', ['updateFeUserByConfirmstring']);
        $feUserService->expects(self::once())
            ->method('updateFeUserByConfirmstring')
            ->will(self::returnValue(true));

        self::assertEquals(
            'success on update',
            $this->getActionMessageByParametersAndFeUserService($parameters, $feUserService)
        );
    }

    /**
     * @group unit
     */
    public function testHandleRequestInModeReturnsCorrectMessageIfUpdateError()
    {
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $parameters->offsetSet('NK_uid', 123);
        $parameters->offsetSet('NK_confirmstring', 'abc');

        $feUserService = $this->getMock('tx_t3users_services_feuser', ['updateFeUserByConfirmstring']);
        $feUserService->expects(self::once())
            ->method('updateFeUserByConfirmstring')
            ->will(self::returnValue(false));

        self::assertEquals(
            'error on update',
            $this->getActionMessageByParametersAndFeUserService($parameters, $feUserService)
        );
    }

    /**
     * @param tx_rnbase_parameters $parameters
     * @param tx_t3users_services_feuser $feUserService
     *
     * @return string
     */
    protected function getActionMessageByParametersAndFeUserService(
        tx_rnbase_parameters $parameters,
        tx_t3users_services_feuser $feUserService = null
    ) {
        $configurationArray = ['feuseredit.' => [
                'mode' => 'check',
            ],
        ];
        $configurations = $this->createConfigurations($configurationArray, 't3users', $parameters);
        $viewData = $configurations->getViewData();

        $action = $this->getMock(
            'tx_t3users_actions_EditFeUser',
            ['getFeUserService']
        );

        if (null === $feUserService) {
            $action->expects(self::never())
                ->method('getFeUserService');
        } else {
            $action->expects(self::once())
                ->method('getFeUserService')
                ->will(self::returnValue($feUserService));
        }

        self::markTestIncomplete('Invalid argument supplied for foreach()');

        return $action->handleRequest($parameters, $configurations, $viewData);
    }
}

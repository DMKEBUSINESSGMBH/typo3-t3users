<?php

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * @author Hannes Bochmann
 */
class tx_t3users_tests_services_emailTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        if (!tx_rnbase_util_Extensions::isLoaded('mkmailer')) {
            $this->markTestSkipped('mkmailer nicht installiert');
        }
        if (!tx_rnbase_util_Extensions::isLoaded('mklib')) {
            $this->markTestSkipped('mklib nicht installiert');
        }

        tx_rnbase::load('tx_t3users_services_email');
        tx_rnbase::load('tx_mkmailer_services_Mail');
        tx_rnbase::load('tx_mklib_tests_Util');

        tx_mklib_tests_Util::prepareTSFE();
        tx_mklib_tests_Util::storeExtConf('mkmailer');
        tx_mklib_tests_Util::setExtConfVar('cronpage', 'unknown', 'mkmailer');
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        if (tx_rnbase_util_Extensions::isLoaded('mkmailer')) {
            tx_mklib_tests_Util::restoreExtConf('mkmailer');
        }
    }

    /**
     * @group unit
     */
    public function testGetMkMailerMailService()
    {
        $this->assertInstanceOf(
            'tx_mkmailer_services_Mail',
            $this->callInaccessibleMethod(
                tx_t3users_util_ServiceRegistry::getEmailService(),
                'getMkMailerMailService'
            )
        );
    }

    /**
     * @group unit
     */
    public function testSendNotificationAboutConfirmationToFeUserRequestsCorrectMailTemplate()
    {
        $mkMailerMailService = $this->getMkMailerMailServiceMock();

        $mkMailerMailService->expects($this->once())
            ->method('getTemplate')
            ->with('t3users_send_confirmation_notification')
            ->will($this->returnValue(
                tx_rnbase::makeInstance('tx_mkmailer_models_Template', [])
            ));

        $this->getMailServiceMock($mkMailerMailService)->sendNotificationAboutConfirmationToFeUser(
            tx_rnbase::makeInstance('tx_t3users_models_feuser', []),
            $this->createConfigurations([], 't3users')
        );
    }

    /**
     * @group unit
     */
    public function testSendNotificationAboutConfirmationToFeUserSpoolsCorrectMailJob()
    {
        $mkMailerMailService = $this->getMkMailerMailServiceMock();

        $templateObj = tx_rnbase::makeInstance(
            'tx_mkmailer_models_Template',
            [
                'contenttext' => '###FEUSER_NAME###',
                'contenthtml' => '###FEUSER_NAME### html',
                'mail_from' => 'typo3site',
                'mail_cc' => 'gchq',
                'mail_bcc' => 'nsa',
                'subject' => 'test mail',
            ]
        );
        $mkMailerMailService->expects($this->once())
            ->method('getTemplate')
            ->with('t3users_send_confirmation_notification')
            ->will($this->returnValue($templateObj));

        $feuser = tx_rnbase::makeInstance('tx_t3users_models_feuser', ['name' => 'John Doe']);
        $receiver = tx_rnbase::makeInstance('tx_mkmailer_receiver_FeUser');
        $receiver->setFeUser($feuser);

        $expectedJob = tx_rnbase::makeInstance('tx_mkmailer_mail_MailJob');
        $expectedJob->addReceiver($receiver);
        $expectedJob->setFrom($templateObj->getFromAddress());
        $expectedJob->setCCs($templateObj->getCcAddress());
        $expectedJob->setBCCs($templateObj->getBccAddress());
        $expectedJob->setSubject($templateObj->getSubject());
        $expectedJob->setContentText('John Doe');
        $expectedJob->setContentHtml('John Doe html');

        $mkMailerMailService->expects($this->once())
            ->method('spoolMailJob')
            ->with($expectedJob);

        $this->getMailServiceMock($mkMailerMailService)->sendNotificationAboutConfirmationToFeUser(
            $feuser,
            $this->createConfigurations([], 't3users')
        );
    }

    /**
     * @return tx_mkmailer_services_Mail
     */
    private function getMkMailerMailServiceMock()
    {
        $mkMailerMailService = $this->getMock(
            'tx_mkmailer_services_Mail',
            ['spoolMailJob', 'getTemplate']
        );

        return $mkMailerMailService;
    }

    /**
     * @return tx_t3users_services_email
     */
    private function getMailServiceMock($mkMailerMailService)
    {
        $mailService = $this->getMock(
            'tx_t3users_services_email',
            ['getMkMailerMailService']
        );

        $mailService->expects($this->once())
            ->method('getMkMailerMailService')
            ->will($this->returnValue($mkMailerMailService));

        return $mailService;
    }
}

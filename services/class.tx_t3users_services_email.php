<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2019 René Nitzsche <dev@dmk-ebusiness.de>
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
 ***************************************************************/

/**
 * Service to send emails.
 *
 * @author René Nitzsche
 * @author Holger Gebhardt
 */
class tx_t3users_services_email extends \TYPO3\CMS\Core\Service\AbstractService
{
    /**
     * Sends newPassword to the feUser.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param string $newPassword
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     */
    public function sendNewPassword($feuser, $newPassword, $configurations, $confId = 'loginbox.')
    {
        if ($this->useMkMailer($configurations, $confId.'email.')) {
            return $this->sendNewPasswordMkMailer($feuser, $newPassword, $configurations, $confId);
        }

        return $this->sendNewPasswordSimple($feuser, $newPassword, $configurations, $confId);
    }

    /**
     * Sends newPassword to the feUser.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param string $newPassword
     * @param \Sys25\RnBase\Configuration\Processor $configurations
     */
    private function sendNewPasswordSimple($feuser, $newPassword, $configurations, $confId)
    {
        // Mail vorbereiten
        $template = $configurations->getLL('loginbox_forgot_infomail');
        $mailMarker = [];
        $mailMarker['###PASSWORD###'] = $newPassword;
        $formatter = $configurations->getFormatter();
        $mailtext = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($template, $mailMarker);

        // Jetzt noch den FeuserMarker
        $marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_util_FeUserMarker');
        $mailtext = $marker->parseTemplate($mailtext, $feuser, $formatter, $confId.'feuser.');
        $emailFrom = $configurations->get($confId.'emailFrom');
        $emailFromName = $configurations->get($confId.'emailFromName');
        $emailReply = $configurations->get($confId.'emailReply');

        $parts = explode(LF, $mailtext, 2);        // First line is subject
        $subject = trim($parts[0]);

        /* @var $mail \Sys25\RnBase\Utility\Email */
        $mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Utility\Email::class);
        $mail->setSubject($subject);

        $mail->setFrom($emailFrom, $emailFromName);
        $mail->setTo($feuser->getEmail());
        $mail->setReplyTo($emailReply);
        $mail->setTextPart($mailtext);
        $mail->send();
    }

    /**
     * @param tx_t3users_models_feuser $feuser
     * @param \Sys25\RnBase\Utility\Link $pwLink
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     * @param string $confId
     */
    public function sendResetPassword(
        $feuser,
        $pwLink,
        $configurations,
        $confId = 'loginbox.'
    ) {
        // aus Kompatibilitätsgründen zu alten Projekten muss
        // der Versand via mkmailer explizit aktiviert werden
        if ($this->useMkMailer($configurations, $confId.'email.')) {
            return $this->sendResetPasswordMkMailer(
                $feuser,
                $pwLink,
                $configurations,
                $confId
            );
        }

        return $this->sendResetPasswordSimple(
            $feuser,
            $pwLink,
            $configurations,
            $confId
        );
    }

    /**
     * Sends a password reset link to the feUser via mkmailer.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param \Sys25\RnBase\Utility\Link $pwLink
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     * @param string $confId
     */
    private function sendResetPasswordMkmailer(
        $feuser,
        $pwLink,
        $configurations,
        $confId
    ) {
        // Das E-Mail-Template holen
        $templatekey = $configurations->get($confId.'resetpassword.mailtemplate');
        $templatekey = empty($templatekey) ? 't3users_resetPassword' : $templatekey;
        $templateObj = tx_mkmailer_util_ServiceRegistry::getMailService()
            ->getTemplate($templatekey);

        // den E-Mail-Empfänger erzeugen
        /* @var $receiver tx_mkmailer_receiver_Email */
        $receiver = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            // @TODO: den receiver konfigurierbar machen!
            'tx_mkmailer_receiver_Email',
            $feuser->getEmail()
        );

        // Einen E-Mail-Job anlegen.
        /* @var $job tx_mkmailer_mail_MailJob */
        $job = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mkmailer_mail_MailJob',
            [$receiver],
            $templateObj
        );

        $markerClass = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Frontend\Marker\SimpleMarker::class);
        $formatter = $configurations->getFormatter();
        $confId .= 'sendmail.';
        $itemName = $configurations->get($confId.'item') ?
            $configurations->get($confId.'item') : 'feuser';

        $markerArray = $subpartArray = $wrappedSubpartArray = [];

        // Daten rendern
        $token = '---';
        $pwLink->label($token);
        $linkMarker = 'RESETLINK';
        $markerArray['###'.$linkMarker.'URL###'] = $pwLink->makeUrl(false);
        $wrappedSubpartArray['###'.$linkMarker.'###'] =
            explode($token, $pwLink->makeTag());
        $formatter = $configurations->getFormatter();
        $mailtext = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached(
            $job->getContentText(),
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray
        );
        $mailhtml = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached(
            $job->getContentHtml(),
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray
        );

        // Mailjob konfigurieren
        $job->setSubject(// Betreff rendern.
            $markerClass->parseTemplate(
                $job->getSubject(),
                $feuser,
                $formatter,
                $confId.strtolower($itemName).'subject.',
                strtoupper($itemName)
            )
        );
        $job->setContentText(// Text Nachricht rendern.
            $markerClass->parseTemplate(
                $mailtext,
                $feuser,
                $formatter,
                $confId.strtolower($itemName).'text.',
                strtoupper($itemName)
            )
        );
        $job->setContentHtml(// HTML Nachricht rendern.
            $markerClass->parseTemplate(
                $mailhtml,
                $feuser,
                $formatter,
                $confId.strtolower($itemName).'html.',
                strtoupper($itemName)
            )
        );

        $job->setFrom($templateObj->getFromAddress());

        // E-Mail für den versand in die Queue legen.
        tx_mkmailer_util_ServiceRegistry::getMailService()->spoolMailJob($job);

        return true;
    }

    /**
     * Sends a password reset link to the feUser.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param \Sys25\RnBase\Utility\Link $pwLink
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     */
    private function sendResetPasswordSimple($feuser, $pwLink, $configurations, $confId)
    {
        // Mail vorbereiten
        $template = $configurations->getLL('loginbox_reset_infomail');
        $templateHtml = trim($configurations->getLL('loginbox_reset_infomail_html'));
        $subpartArray = [];
        $wrappedSubpartArray = [];

        $token = '---';
        $pwLink->label($token);
        $linkMarker = 'RESETLINK';
        $markerArray = [];
        $markerArray['###'.$linkMarker.'URL###'] = $pwLink->makeUrl(false);
        $wrappedSubpartArray['###'.$linkMarker.'###'] = explode($token, $pwLink->makeTag());
        $formatter = $configurations->getFormatter();
        $mailtext = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
        if ($templateHtml) {
            $mailhtml = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($templateHtml, $markerArray, $subpartArray, $wrappedSubpartArray);
        }

        // Jetzt noch den FeuserMarker
        $marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_util_FeUserMarker');
        $mailtext = $marker->parseTemplate($mailtext, $feuser, $formatter, $confId.'feuser.');
        $mailhtml = $marker->parseTemplate($mailhtml, $feuser, $formatter, $confId.'feuser.');
        $emailFrom = $configurations->get($confId.'emailFrom');
        $emailFromName = $configurations->get($confId.'emailFromName');

        $this->sendInstant($mailtext, $mailhtml, $feuser->getEmail(), $emailFrom, $emailFromName);
    }

    private function sendInstant($mailtext, $mailhtml, $emailTo, $emailFrom, $emailFromName, $emailReply = null)
    {
        $parts = explode(LF, $mailtext, 2);        // First line is subject
        $subject = trim($parts[0]);
        $mailtext = trim($parts[1]);
        if ($mailhtml) {
            $parts = explode(LF, $mailhtml, 2);        // First line is subject
            $subject = trim($parts[0]);
            $mailhtml = trim($parts[1]);
        }
        /* @var $mail \Sys25\RnBase\Utility\Email */
        $mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Utility\Email::class);
        $mail->setSubject($subject);

        $mail->setFrom($emailFrom, $emailFromName);
        $mail->setTo($emailTo);
        if ($emailReply) {
            $mail->setReplyTo($emailReply);
        }
        if ($mailtext) {
            $mail->setTextPart($mailtext);
        }
        if ($mailhtml) {
            $mail->setHtmlPart($mailhtml);
        }
        $mail->send();
    }

    /**
     * Sends newPassword to the feUser.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param string $newPassword
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     */
    private function sendNewPasswordMkMailer($feuser, $newPassword, $configurations, $confId)
    {
        // TODO: Make alternative mailtemplate via Resources/Private/Language/locallang.xlf possible, use the lines below
        // $emailFrom = $this->configurations->get('loginbox.emailFrom');
        // $emailFromName = $this->configurations->get('loginbox.emailFromName');
        // $emailReply = $this->configurations->get('loginbox.emailReply');

        $mailSrv = tx_mkmailer_util_ServiceRegistry::getMailService();
        $templateObj = $mailSrv->getTemplate('t3users_sendnewpassword');

        $markerArray = [];
        $markerArray['###PASSWORD###'] = $newPassword;
        $wrappedSubpartArray = $subpartArray = [];

        $messageTxt = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($templateObj->getContentText(), $markerArray, $subpartArray, $wrappedSubpartArray);
        $messageHtml = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($templateObj->getContentHtml(), $markerArray, $subpartArray, $wrappedSubpartArray);

        // feUser-Marker werden mittels Marker-Klasse ersetzt
        $formatter = $configurations->getFormatter();
        $marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_util_FeUserMarker');
        $messageTxt = $marker->parseTemplate($messageTxt, $feuser, $formatter, $confId.'feuser.');
        $messageHtml = $marker->parseTemplate($messageHtml, $feuser, $formatter, $confId.'feuser.');

        $receiver = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mkmailer_receiver_FeUser');
        $receiver->setFeUser($feuser);

        $job = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mkmailer_mail_MailJob');
        $job->addReceiver($receiver);
        $job->setFrom($templateObj->getFromAddress());
        $job->setCCs($templateObj->getCcAddress());
        $job->setBCCs($templateObj->getBccAddress());
        $job->setSubject($templateObj->getSubject());
        $job->setContentText($messageTxt);
        $job->setContentHtml($messageHtml);
        $mailSrv->spoolMailJob($job);
    }

    /**
     * Send edited feUser data to his email for confirmation.
     *
     * @param tx_t3users_models_feuser $feUser
     * @param array $data
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     */
    public function sendEditedData($feUser, $data, $configurations, $confId = 'feuseredit.')
    {
        $mailSrv = tx_mkmailer_util_ServiceRegistry::getMailService();
        $templateObj = $mailSrv->getTemplate('t3users_confirmdatachange');

        // Link zur Bestätigungsseite erstellen
        $token = md5(microtime());
        $link = $configurations->createLink();
        $link->label($token);
        $doubleOptInPage = intval($configurations->get($confId.'doubleoptin.pid'));
        $link->destination($doubleOptInPage ? $doubleOptInPage : $GLOBALS['TSFE']->id);
        $link->setAbsUrl(true);

        $markerArray = $wrappedSubpartArray = $linkParams = [];
        // Daten extrahieren
        // Daten, die geändert werden sollen (also mit Link mitgeschickt werden)
        foreach ($data as $key => $value) {
            $markerArray['###'.strtoupper($key).'###'] = $value;
            $linkParams['NK_'.$key] = $value;
        }
        $link->parameters($linkParams);

        $linkMarker = 'MAILCONFIRM_LINK';
        $wrappedSubpartArray['###'.$linkMarker.'###'] = explode($token, $link->makeTag());

        $markerArray = $data;
        $markerArray['###'.$linkMarker.'URL###'] = $link->makeUrl(false);
        $subpartArray = [];

        $messageTxt = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($templateObj->getContentText(), $markerArray, $subpartArray, $wrappedSubpartArray);
        $messageHtml = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($templateObj->getContentHtml(), $markerArray, $subpartArray, $wrappedSubpartArray);

        // feUser-Marker werden mittels Marker-Klasse ersetzt
        $formatter = $configurations->getFormatter();
        $marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_util_FeUserMarker');
        $messageTxt = $marker->parseTemplate($messageTxt, $feUser, $formatter, $confId.'feuser.');
        $messageHtml = $marker->parseTemplate($messageHtml, $feUser, $formatter, $confId.'feuser.');

        // haben sich die Daten geändert dann nehmen wir einen anderen receiver
        // um den nochmaligen versand an die neue adresse zu verhindern
        if ($feUser->getProperty('email') != $data['email']) {
            $receiver = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_receiver_FeUserChanged');
        } else {
            $receiver = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mkmailer_receiver_FeUser');
        }
        $receiver->setFeUser($feUser);

        $job = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mkmailer_mail_MailJob');
        $job->addReceiver($receiver);
        $job->setFrom($templateObj->getFromAddress());
        $job->setCCs($templateObj->getCcAddress());
        $job->setBCCs($templateObj->getBccAddress());
        $job->setSubject($templateObj->getSubject());
        $job->setContentText($messageTxt);
        $job->setContentHtml($messageHtml);
        $mailSrv->spoolMailJob($job);
    }

    /**
     * Sends confirmLink to the feUser.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param \Sys25\RnBase\Utility\Link $confirmLink
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     * @param string $confId
     */
    public function sendConfirmLink($feuser, $confirmLink, $configurations, $confId = 'loginbox.')
    {
        if ($this->useMkMailer($configurations, $confId.'email.')) {
            return $this->sendConfirmLinkMkMailer($feuser, $confirmLink, $configurations, $confId);
        }

        return $this->sendConfirmLinkSimple($feuser, $confirmLink, $configurations, $confId);
    }

    /**
     * Sends newPassword to the feUser.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param \Sys25\RnBase\Utility\Link $confirmLink
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     * @param string $confId
     */
    private function sendConfirmLinkSimple($feuser, $confirmLink, $configurations, $confId)
    {
        // Mail vorbereiten
        $mailMarker = $mailWrappedSubpart = [];
        $mailMarker['###CONFIRMLINKURL###'] = $confirmLink->makeUrl();
        $mailWrappedSubpart['###CONFIRMLINK###'] = explode($confirmLink->getLabel(), $confirmLink->makeTag());

        $linkMarker = 'MAILCONFIRM_LINK';
        $mailWrappedSubpart['###'.$linkMarker.'###'] = $mailWrappedSubpart['###CONFIRMLINK###'];
        $mailMarker['###'.$linkMarker.'URL###'] = $mailMarker['###CONFIRMLINKURL###'];

        $mailMarker['###SITENAME###'] = $configurations->get('siteName');

        // Template laden
        $template = $configurations->getLL('registration_confirmation_mail');
        if (!$template) {
            // Wenn das Template nicht als Label gesetzt ist, suchen wir eine Templatedatei
            $templatePath = $configurations->get($confId.'template.file', true);
            if ($templatePath) {
                $subpart = $configurations->get($confId.'template.subpart', true);
                $subpart = $subpart ? $subpart : '###CONFIRMATIONMAIL###';
                $template = trim(\Sys25\RnBase\Frontend\Marker\Templates::getSubpartFromFile($templatePath, $subpart));
            }
        }
        $templateHtml = $configurations->getLL('registration_confirmation_mail_html');
        if (!$templateHtml) {
            // Wenn das Template nicht als Label gesetzt ist, suchen wir eine Templatedatei
            $templatePath = $configurations->get($confId.'templatehtml.file', true);
            if ($templatePath) {
                $subpart = $configurations->get($confId.'templatehtml.subpart', true);
                $subpart = $subpart ? $subpart : '###CONFIRMATIONMAILHTML###';
                $templateHtml = trim(\Sys25\RnBase\Frontend\Marker\Templates::getSubpartFromFile($templatePath, $subpart));
            }
        }
        $mailtextCC = '';
        if ($cc = $configurations->get($confId.'cc')) {
            $templateCC = $configurations->getLL('registration_confirmation_mail_cc');
            $templateCC = $templateCC ? $templateCC : $template;
        }

        // Links ersetzen
        $mailtext = '';
        if ($template) {
            $mailtext = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($template, $mailMarker, [], $mailWrappedSubpart);
        }
        $mailhtml = '';
        if ($templateHtml) {
            $mailhtml = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($templateHtml, $mailMarker, [], $mailWrappedSubpart);
        }
        if ($templateCC) {
            $mailtextCC = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($templateCC, $mailMarker, [], $mailWrappedSubpart);
        }

        $formatter = $configurations->getFormatter();
        // Jetzt noch den FeuserMarker
        $marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_util_FeUserMarker');
        if ($mailtext) {
            $mailtext = $marker->parseTemplate($mailtext, $feuser, $formatter, $confId.'feuser.');
        }
        if ($mailhtml) {
            $mailhtml = $marker->parseTemplate($mailhtml, $feuser, $formatter, $confId.'feuser.');
        }
        if ($mailtextCC) {
            $mailtextCC = $marker->parseTemplate($mailtextCC, $feuser, $formatter, $confId.'feuser.');
        }
        $emailFrom = $configurations->get($confId.'from');
        $emailFromName = $configurations->get($confId.'fromName');
        $emailReply = $configurations->get($confId.'reply');

        $this->sendInstant($mailtext, $mailhtml, $feuser->getEmail(), $emailFrom, $emailFromName, $emailReply);
        if ($cc) {
            $this->sendInstant($mailtext, $mailhtml, $cc, $emailFrom, $emailFromName, $emailReply);
        }
    }

    /**
     * Sends newPassword to the feUser.
     *
     * @param tx_t3users_models_feuser $feuser
     * @param \Sys25\RnBase\Utility\Link $confirmLink
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     * @param string $confId
     */
    private function sendConfirmLinkMkMailer($feuser, $confirmLink, $configurations, $confId)
    {
        // TODO: Make alternative mailtemplate via Resources/Private/Language/locallang.xlf possible, use the lines below
        // $emailFrom = $this->configurations->get('loginbox.emailFrom');
        // $emailFromName = $this->configurations->get('loginbox.emailFromName');
        // $emailReply = $this->configurations->get('loginbox.emailReply');

        $templateKey = $configurations->get($confId.'mkmailerTemplateKey');
        $templateKey = $templateKey ?: 't3users_sendconfirmlink';
        $mailSrv = tx_mkmailer_util_ServiceRegistry::getMailService();
        $templateObj = $mailSrv->getTemplate($templateKey);

        $markerArray = [];
        $wrappedSubpartArray = [];
        $subpartArray = [];
        $markerArray['###CONFIRMLINKURL###'] = $confirmLink->makeUrl(false);
        $wrappedSubpartArray['###CONFIRMLINK###'] = explode($confirmLink->getLabel(), $confirmLink->makeTag());

        $messageTxt = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($templateObj->getContentText(), $markerArray, $subpartArray, $wrappedSubpartArray);
        $messageHtml = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($templateObj->getContentHtml(), $markerArray, $subpartArray, $wrappedSubpartArray);

        // feUser-Marker werden mittels Marker-Klasse ersetzt
        $formatter = $configurations->getFormatter();
        $marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_util_FeUserMarker');
        $messageTxt = $marker->parseTemplate($messageTxt, $feuser, $formatter, $confId.'feuser.');
        $messageHtml = $marker->parseTemplate($messageHtml, $feuser, $formatter, $confId.'feuser.');

        $receiver = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mkmailer_receiver_FeUser');
        $receiver->setFeUser($feuser);

        $job = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mkmailer_mail_MailJob');
        $job->addReceiver($receiver);
        $job->setFrom($templateObj->getFromAddress());
        $job->setCCs($templateObj->getCcAddress());
        $job->setBCCs($templateObj->getBccAddress());
        $job->setSubject($templateObj->getSubject());
        $job->setContentText($messageTxt);
        $job->setContentHtml($messageHtml);
        $mailSrv->spoolMailJob($job);
    }

    /**
     * @param tx_t3users_models_feuser $feuser
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     * @param string $confId
     *
     * Beispiel Mailtemplate:
     *
        Guten Tag ###FEUSER_NAME###
        ihre Registrierung auf Serviceoasen wurde freigegeben. Sie können sich nun unter ###FEUSER_LOGINLINK###diesem Link###FEUSER_LOGINLINK### anmelden.
        Für Fragen stehen wir Ihnen gerne jederzeit zur Verfügung.
     */
    public function sendNotificationAboutConfirmationToFeUser(
        tx_t3users_models_feuser $feuser,
        \Sys25\RnBase\Configuration\ProcessorInterface $configurations,
        $confId = 'showregistration.'
    ) {
        $mailSrv = $this->getMkMailerMailService();
        $templateObj = $mailSrv->getTemplate('t3users_send_confirmation_notification');

        // feUser-Marker werden mittels Marker-Klasse ersetzt
        $formatter = $configurations->getFormatter();
        $marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_util_FeUserMarker');
        $messageTxt = $marker->parseTemplate(
            $templateObj->getContentText(),
            $feuser,
            $formatter,
            $confId.'feuser.'
        );
        $messageHtml = $marker->parseTemplate(
            $templateObj->getContentHtml(),
            $feuser,
            $formatter,
            $confId.'feuser.'
        );

        $receiver = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mkmailer_receiver_FeUser');
        $receiver->setFeUser($feuser);

        $job = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mkmailer_mail_MailJob');
        $job->addReceiver($receiver);
        $job->setFrom($templateObj->getFromAddress());
        $job->setCCs($templateObj->getCcAddress());
        $job->setBCCs($templateObj->getBccAddress());
        $job->setSubject($templateObj->getSubject());
        $job->setContentText($messageTxt);
        $job->setContentHtml($messageHtml);
        $mailSrv->spoolMailJob($job);
    }

    /**
     * @return tx_mkmailer_services_Mail
     */
    protected function getMkMailerMailService()
    {
        return tx_mkmailer_util_ServiceRegistry::getMailService();
    }

    /**
     * Whether or not use mkmailer for email processing.
     *
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     * @param string $confId there should be an option "useMkmailer" below this confId
     *
     * @return bool
     */
    private function useMkMailer($configurations, $confId)
    {
        return \Sys25\RnBase\Utility\Extensions::isLoaded('mkmailer')
                && $configurations->getBool($confId.'useMkmailer');
    }
}

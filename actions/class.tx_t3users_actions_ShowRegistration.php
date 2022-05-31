<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2017 Rene Nitzsche (dev@dmk-ebusiness.de)
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
 * Controller für die Neuregistrierung.
 */
class tx_t3users_actions_ShowRegistration extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{
    private $feuser;
    private $afterRegistrationPID;
    private $userDataSaved = false;

    private \Sys25\RnBase\Configuration\ConfigurationInterface $configurations;

    public function handleRequest(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $parameters = $request->getParameters();
        $this->configurations = $request->getConfigurations();
        $viewData = $request->getViewContext();

        $this->assertMkforms();
        $hideForm = false;
        $viewData->offsetSet('part', 'REGISTER');
        $confirm = $parameters->get('confirm');
        $userUid = $parameters->getInt('uid');
        if ($adminReviewMail = $this->configurations->get($this->getConfId().'adminReviewMail')) {
            if ($this->sendAdminReviewMail($userUid, $confirm, $adminReviewMail, $request)) {
                $viewData->offsetSet('part', 'ADMINREVIEWMAILSENT');
            } else {
                $viewData->offsetSet('part', 'ADMINREVIEWMAILSENTALREADY');
            }
            $viewData->offsetSet('confirmed', true);
        } elseif ($confirm) {
            $hideForm = true;
            // Load instance
            $feuser = tx_t3users_models_feuser::getInstance($userUid);
            $usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
            // Set config
            $options = [];
            // Das sollte MIT Pfad gesetzt werden...
            $options['successgroupsadd'] = $this->configurations->get('userGroupAfterConfirmation');
            $options['successgroupsremove'] = $this->configurations->get('userGroupUponRegistration');
            $options['configurations'] = $this->configurations;
            $options['confid'] = $this->getConfId();
            $confirmed = $usrSrv->confirmUser($feuser, $confirm, $options);
            if ($confirmed) {
                $viewData->offsetSet('part', 'CONFIRMED');
                $viewData->offsetSet('confirmed', $feuser);
            } else {
                $viewData->offsetSet('part', 'CONFIRMFAILED');
                $viewData->offsetSet('confirmed', '0');
            }

            if ($this->configurations->get($this->getConfId().'notifyUserAboutConfirmation')) {
                tx_t3users_util_ServiceRegistry::getEmailService()
                    ->sendNotificationAboutConfirmationToFeUser($feuser, $this->configurations);
            }
        } elseif ($parameters->offsetGet('NK_saved')) {
            $viewData->offsetSet('part', 'REGISTERFINISHED');
            $hideForm = true;
        }
        $editors = $this->getEditors($parameters, $this->configurations, $hideForm);
        // elseif($parameters->offsetGet('NK_saved')) {
        if ($this->userDataSaved) {
            // Redirect nach dem Versand der Email
            $link = $this->configurations->createLink();
            $link->destination($GLOBALS['TSFE']->id); // Link auf aktuelle Seite
            // Zusätzlich Parameter für Finished setzen
            $link->parameters(['NK_saved' => '1', 'NK_reguser' => $uid]);
            $redirect_url = $link->makeUrl(false);
            header('Location: '.\Sys25\RnBase\Utility\Network::locationHeaderUrl($redirect_url));
        }
        // index.php?id=38&amp;rnuser%5BNK_confirm%5D=5d52036ce724a231ab8d90ab120638db&amp;rnuser%5BNK_uid%5D=4&amp;cHash=c19b590e9c

        $viewData->offsetSet('editors', $editors);
    }

    protected function assertMkforms()
    {
        if (!\Sys25\RnBase\Utility\Extensions::isLoaded('mkforms')) {
            throw new Exception('mkforms is not installed');
        }
    }

    /**
     * @param int $userUid
     * @param string $confirmString
     * @param string $adminReviewMail
     * @param \Sys25\RnBase\Frontend\Request\RequestInterface $request
     *
     * @return bool
     */
    protected function sendAdminReviewMail($userUid, $confirmString, $adminReviewMail, \Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $feuser = tx_t3users_models_feuser::getInstance($userUid);
        if ($confirmString != $feuser->getProperty('confirmstring')) {
            return false;
        }
        // else
        $usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
        $confirmString = $this->getConfirmString();
        $feuser->setProperty('confirmstring', $confirmString);
        $usrSrv->handleUpdate($feuser, ['confirmstring' => $confirmString]);
        // adminEmail injizieren
        $feuser->setProperty('email', $adminReviewMail);
        $this->sendConfirmationMail($feuser, true);

        return true;
    }

    /**
     * @param \Sys25\RnBase\Frontend\Request\ParametersInterface $parameters
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     * @param bool $hide
     *
     * @return string[]
     */
    protected function getEditors(
        \Sys25\RnBase\Frontend\Request\ParametersInterface $parameters,
        \Sys25\RnBase\Configuration\ConfigurationInterface $configurations,
        $hide
    ) {
        $editors = ['FORM' => ''];
        if ($hide) {
            return $editors;
        }
        $regForm = tx_mkforms_forms_Factory::createForm('registration');
        $xmlfile = $configurations->get($this->getConfId().'formxml');
        $xmlfile = $xmlfile ? $xmlfile : \Sys25\RnBase\Utility\Extensions::extPath('t3users').'Resources/Private/Forms/registration.xml';
        $regForm->init($this, $xmlfile, false, $configurations, $this->getConfId().'formconfig.');
        $editors['FORM'] = $regForm->render();

        return $editors;
    }

    /**
     * Set PID.
     *
     * @param array $params
     * @param tx_ameosformidable $form
     */
    public function handleBeforeUpdateDB($params, $form)
    {
        $params['confirmstring'] = $this->getConfirmString();
        $pid = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $this->configurations->get('feuserPages'));
        $params['pid'] = (is_array($pid) && count($pid)) ? $pid[0] : 0;
        $params['disable'] = 1;
        $params['tstamp'] = $GLOBALS['EXEC_TIME'];
        $params['crdate'] = $params['tstamp'];
        $groupId = intval($this->configurations->get('userGroupUponRegistration'));
        $params['usergroup'] = $groupId;
        $params['name'] = trim($params['first_name'].' '.$params['last_name']);
        $usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();

        $pass = $params[isset($params['password123']) ? 'password123' : 'password'];
        $params['password'] = $usrSrv->encryptPassword($pass);
        unset($params['password123']);

        \Sys25\RnBase\Utility\Misc::callHook(
            't3users',
            'showRegistration_beforeUpdateDB_hook',
            [
                'params' => &$params,
                'form' => $form,
            ],
            $this
        );

        return $params;
    }

    /**
     * @return string
     */
    protected function getConfirmString()
    {
        return md5(uniqid());
    }

    /**
     * User is saved. Send confirmation mail.
     *
     * @param array $params
     * @param tx_ameosformidable $form
     */
    public function handleUpdateDB($params, $form)
    {
        $uid = $form->oDataHandler->newEntryId;

        \Sys25\RnBase\Utility\Misc::callHook(
            't3users',
            'showRegistration_beforeSendConfirmationMail_hook',
            [
                'params' => &$params,
                'form' => $form,
                'newEntryId' => $uid,
            ],
            $this
        );

        // FIXME:
        // In tx_t3users_services_email::sendConfirmLink() macht im
        // Prinzip dasselbe. Das sollte vereinheitlicht werden
        $feuser = tx_t3users_models_feuser::getInstance($uid);
        $this->sendConfirmationMail($feuser);
        $this->userDataSaved = true;
    }

    /**
     * @param tx_t3users_models_feuser $feuser
     * @param $isAdminNotification
     * @param \Sys25\RnBase\Frontend\Request\RequestInterface $request
     */
    protected function sendConfirmationMail(tx_t3users_models_feuser $feuser, $isAdminNotification = false, \Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $feUserUid = $feuser->getUid();
        $feUserData = $feuser->getProperty();

        // Zusätzlich Parameter für Finished setzen
        $parameters = [
                'NK_confirm' => $feUserData['confirmstring'],
                'NK_uid' => $feUserUid,
        ];

        // Mail schicken
        $link = $request->getConfigurations()->createLink();
        $link->initByTS($request->getConfigurations(), $this->getConfId().'links.mailconfirm.', $parameters);
        $token = md5(microtime());
        $link->label($token);

        tx_t3users_util_ServiceRegistry::getEmailService()->sendConfirmLink(
            $feuser,
            $link,
            $request->getConfigurations(),
            $this->getConfId().($isAdminNotification ? 'admin' : '').'email.'
        );
    }

    public function nextPage($params)
    {
        $this->regValues = $params;
    }

    // TODO: remove method
    public function getValue($param)
    {
        if ('username' == $param['col']) {
            return 'Testuser';
        }

        return $this->regValues[$param['col']];
    }

    public function getTemplateName()
    {
        return 'showregistration';
    }

    public function getViewClassName()
    {
        return 'tx_t3users_views_ShowRegistration';
    }
}

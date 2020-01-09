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

tx_rnbase::load('tx_rnbase_util_DB');
tx_rnbase::load('tx_rnbase_action_BaseIOC');
tx_rnbase::load('tx_t3users_models_feuser');
tx_rnbase::load('Tx_Rnbase_Utility_Strings');
tx_rnbase::load('tx_rnbase_util_Files');
tx_rnbase::load('tx_rnbase_util_Templates');
tx_rnbase::load('tx_rnbase_util_Network');
tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * Controller für die Neuregistrierung
 */
class tx_t3users_actions_ShowRegistration extends tx_rnbase_action_BaseIOC
{
    private $feuser;
    private $afterRegistrationPID;
    private $userDataSaved = false;

    public function handleRequest(&$parameters, &$configurations, &$viewData)
    {
        $this->assertMkforms();
        $hideForm = false;
        $viewData->offsetSet('part', 'REGISTER');
        $confirm = $parameters->get('confirm');
        $userUid = $parameters->getInt('uid');
        if ($adminReviewMail = $configurations->get($this->getConfId(). 'adminReviewMail')) {
            if ($this->sendAdminReviewMail($userUid, $confirm, $adminReviewMail)) {
                $viewData->offsetSet('part', 'ADMINREVIEWMAILSENT');
            } else {
                $viewData->offsetSet('part', 'ADMINREVIEWMAILSENTALREADY');
            }
            // Wo kommt denn hier der $feuser her?
            $viewData->offsetSet('confirmed', $feuser);
        } elseif ($confirm) {
            $hideForm = true;
            // Load instance
            $feuser = tx_t3users_models_feuser::getInstance($userUid);
            $usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
            // Set config
            $options = array();
            // Das sollte MIT Pfad gesetzt werden...
            $options['successgroupsadd'] = $configurations->get('userGroupAfterConfirmation');
            $options['successgroupsremove'] = $configurations->get('userGroupUponRegistration');
            $options['configurations'] = $configurations;
            $options['confid'] = $this->getConfId();
            $confirmed = $usrSrv->confirmUser($feuser, $confirm, $options);
            if ($confirmed) {
                $viewData->offsetSet('part', 'CONFIRMED');
                $viewData->offsetSet('confirmed', $feuser);
            } else {
                $viewData->offsetSet('part', 'CONFIRMFAILED');
                $viewData->offsetSet('confirmed', '0');
            }


            if ($configurations->get($this->getConfId(). 'notifyUserAboutConfirmation')) {
                tx_t3users_util_ServiceRegistry::getEmailService()
                    ->sendNotificationAboutConfirmationToFeUser($feuser, $configurations);
            }
        } elseif ($parameters->offsetGet('NK_saved')) {
            $viewData->offsetSet('part', 'REGISTERFINISHED');
            $hideForm = true;
        }
        $editors = $this->getEditors($parameters, $configurations, $hideForm);
        //elseif($parameters->offsetGet('NK_saved')) {
        if ($this->userDataSaved) {
            // Redirect nach dem Versand der Email
            $link = $configurations->createLink();
            $link->destination($GLOBALS['TSFE']->id); // Link auf aktuelle Seite
        // Zusätzlich Parameter für Finished setzen
            $link->parameters(array('NK_saved' => '1', 'NK_reguser' => $uid));
            $redirect_url = $link->makeUrl(false);
            header('Location: ' . tx_rnbase_util_Network::locationHeaderUrl($redirect_url));
        }
    // index.php?id=38&amp;rnuser%5BNK_confirm%5D=5d52036ce724a231ab8d90ab120638db&amp;rnuser%5BNK_uid%5D=4&amp;cHash=c19b590e9c


        $viewData->offsetSet('editors', $editors);
    }
    protected function assertMkforms()
    {
        if (!tx_rnbase_util_Extensions::isLoaded('mkforms')) {
            throw new Exception('mkforms is not installed');
        }
    }

    /**
     * @param int $userUid
     * @param string $confirmString
     * @param string $adminReviewMail
     *
     * @return bool
     */
    protected function sendAdminReviewMail($userUid, $confirmString, $adminReviewMail)
    {
        $feuser = tx_t3users_models_feuser::getInstance($userUid);
        if ($confirmString != $feuser->getProperty('confirmstring')) {
            return false;
        }
        //else
        $usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
        $confirmString = $this->getConfirmString();
        $feuser->setProperty('confirmstring', $confirmString);
        $usrSrv->handleUpdate($feuser, ['confirmstring'  => $confirmString]);
        //adminEmail injizieren
        $feuser->setProperty('email', $adminReviewMail);
        $this->sendConfirmationMail($feuser, true);

        return true;
    }

    /**
     *
     * @param \Sys25\RnBase\Frontend\Request\ParametersInterface $parameters
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param boolean $hide
     * @return string[]
     */
    protected function getEditors(
        \Sys25\RnBase\Frontend\Request\ParametersInterface $parameters,
        \Sys25\RnBase\Configuration\ConfigurationInterface $configurations,
        $hide
    ) {
        $editors = array('FORM' => '');
        if ($hide) {
            return $editors;
        }
        tx_rnbase::load('tx_mkforms_forms_Factory');
        $regForm = tx_mkforms_forms_Factory::createForm('registration');
        $xmlfile = $configurations->get($this->getConfId(). 'formxml');
        $xmlfile = $xmlfile ? $xmlfile : tx_rnbase_util_Extensions::extPath('t3users') . 'Resources/Private/Forms/registration.xml';
        $regForm->init($this, $xmlfile, false, $configurations, $this->getConfId().'formconfig.');
        $editors['FORM'] = $regForm->render();

        return $editors;
    }

    /**
     * Set PID
     *
     * @param array $params
     * @param tx_ameosformidable $form
     */
    public function handleBeforeUpdateDB($params, $form)
    {
        $params['confirmstring'] = $this->getConfirmString();
        $pid = Tx_Rnbase_Utility_Strings::intExplode(',', $this->getConfigurations()->get('feuserPages'));
        $params['pid'] = (is_array($pid) && count($pid)) ? $pid[0] : 0;
        $params['disable'] = 1;
        $params['tstamp'] = $GLOBALS['EXEC_TIME'];
        $params['crdate'] = $params['tstamp'];
        $groupId = intval($this->getConfigurations()->get('userGroupUponRegistration'));
        $params['usergroup'] = $groupId;
        $params['name'] = trim($params['first_name'] . ' ' .$params['last_name']);
        $usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();

        $pass = $params[ isset($params['password123']) ? 'password123' : 'password' ];
        $params['password'] = $usrSrv->encryptPassword($pass);
        unset($params['password123']);

        tx_rnbase_util_Misc::callHook(
            't3users',
            'showRegistration_beforeUpdateDB_hook',
            array(
                'params' => &$params,
                'form' => $form
            ),
            $this
        );

        return $params;
    }

    /**
     *
     * @return string
     */
    protected function getConfirmString()
    {
        return md5(uniqid());
    }


    /**
     * User is saved. Send confirmation mail
     *
     * @param array $params
     * @param tx_ameosformidable $form
     */
    public function handleUpdateDB($params, $form)
    {
        $uid = $form->oDataHandler->newEntryId;

        tx_rnbase_util_Misc::callHook(
            't3users',
            'showRegistration_beforeSendConfirmationMail_hook',
            array(
                'params' => &$params,
                'form' => $form,
                'newEntryId' => $uid
            ),
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
     *
     * @return void
     */
    protected function sendConfirmationMail(tx_t3users_models_feuser $feuser, $isAdminNotification = false)
    {
        $feUserUid = $feuser->getUid();
        $feUserData = $feuser->getProperty();

        // Zusätzlich Parameter für Finished setzen
        $parameters = array(
                'NK_confirm' => $feUserData['confirmstring'],
                'NK_uid' => $feUserUid
        );

        // Mail schicken
        $link = $this->getConfigurations()->createLink();
        $link->initByTS($this->getConfigurations(), $this->getConfId().'links.mailconfirm.', $parameters);
        $token = md5(microtime());
        $link->label($token);

        tx_t3users_util_ServiceRegistry::getEmailService()->sendConfirmLink(
            $feuser,
            $link,
            $this->getConfigurations(),
            $this->getConfId() . ($isAdminNotification ? 'admin' : '') . 'email.'
        );
    }

    private function parseMailTemplate(
        $template,
        array $markerArray,
        array $subpartArray,
        array $wrappedSubpartArray,
        array $feUserData
    ) {
        $mailtext = tx_rnbase_util_Templates::substituteMarkerArrayCached(
            $template,
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray
        );

        $markerArray = array();
        tx_rnbase::load('tx_rnbase_util_BaseMarker');
        tx_rnbase_util_BaseMarker::callModules(
            $mailtext,
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray,
            $feUserData,
            $this->getConfigurations()->getFormatter()
        );

        return tx_rnbase_util_Templates::substituteMarkerArrayCached(
            $mailtext,
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray
        );
    }

    public function nextPage($params)
    {
        $this->regValues = $params;
    }

    // TODO: remove method
    public function getValue($param)
    {
        if ($param['col'] == 'username') {
            return 'Testuser';
        }

        return $this->regValues[$param['col']];
    }

  /**
   * The HTML-Template for registration form
   *
   * @return string path name
   *
   * @deprecated der template pfad sollte im XML gesetzt werden da diese methode
   * bei ajax calls nicht funktioniert
   */
    public function getFormTemplatePath()
    {
        $path = tx_rnbase_util_Files::getFileAbsFileName($this->getConfigurations()->get($this->getConfId().'form'));

        return $path;
    }

  /**
   * FIXME: WARUM wird hier nicht showregistration verwendet?
   * (non-PHPdoc)
   * @see tx_rnbase_action_BaseIOC::getTemplateName()
   */
    public function getTemplateName()
    {
        return 'showregistration';
    }
    public function getViewClassName()
    {
        return 'tx_t3users_views_ShowRegistration';
    }
}

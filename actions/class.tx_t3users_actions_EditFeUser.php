<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2017 Rene Nitzsche (dev@dmk-ebusiness.de)
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
 * Controller for edit form of FE-User.
 */
class tx_t3users_actions_EditFeUser extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{
    /** @var \Sys25\RnBase\Configuration\Processor */
    protected $configurations;

    public function handleRequest(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $parameters = $request->getParameters();
        $configurations = $request->getConfigurations();
        $viewData = $request->getViewContext();

        $this->configurations = $configurations;
        // Bei Modus "check" werden die Daten aus der Url in die DB geschrieben (wenn
        // möglich). Ansonsten wird ganz normal die Form geparsed.
        // @TODO das ist sehr gefährlich. Im Formular könnten Daten validiert wurden sein
        // und dann im Link geändert werden. Die Daten sollten nicht im Link übertragen werden
        // sondern in einem eigenen Feld etc. in der DB zwischengespeichert werden.
        if ('check' == $this->configurations->get($this->getConfId().'mode')) {
            // uid und confirmstring sind nicht in tca weshalb wir sie
            // fest auslesen müssen
            $uid = $parameters->offsetGet('NK_uid');
            $confirmstring = $parameters->offsetGet('NK_confirmstring');

            if (empty($uid) || empty($confirmstring)) {
                return $configurations->getLL('msg_change_error');
            }

            // leeres Model um alle DB Felder auszulesen
            $feUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_models_feuser', ['uid' => 0]);

            // für jedes Feld in der DB prüfen ob ein Wert übermittelt wurde
            foreach ($feUser->getColumnNames() as $cols) {
                if ($parameters->offsetExists('NK_'.$cols)) {
                    $params[$cols] = $parameters->offsetGet('NK_'.$cols);
                }
            }

            // zur Sicherheit email == username setzen
            if (!empty($params['email'])) {
                $params['username'] = $params['email'];
            }
            // confirmstring wieder auf '' setzen
            $params['confirmstring'] = '';
            // und ab damit
            $feUserSrv = $this->getFeUserService();
            if ($feUserSrv->updateFeUserByConfirmstring($uid, $confirmstring, $params)) {
                return $configurations->getLL('msg_change_success');
            } else {
                return $configurations->getLL('msg_change_error');
            }
        } else {
            $feuser = tx_t3users_models_feuser::getCurrent();
            if (!$feuser) {
                return $configurations->getLL('notLoggedIn');
            }

            $form = $this->getEditors($parameters, $configurations, $feuser);
            $viewData->offsetSet('form', $form->render());
            $viewData->offsetSet('user', $feuser);
        }
    }

    /**
     * @return tx_t3users_services_feuser
     */
    protected function getFeUserService()
    {
        return tx_t3users_util_ServiceRegistry::getFeUserService();
    }

    /**
     * Liefert den Editor.
     *
     * @param array $parameters
     * @param \Sys25\RnBase\Configuration\Processor $configurations
     * @param tx_t3users_models_feuser $item
     *
     * @return tx_mkforms_forms_IForm
     */
    private function getEditors($parameters, $configurations, $item)
    {
        if (!\Sys25\RnBase\Utility\Extensions::isLoaded('mkforms')) {
            throw new Exception('mkforms ist nicht installiert, wird aber benötigt für das Bearbeitungsformular');
        }

        $this->form = tx_mkforms_forms_Factory::createForm('');
        $formXml = $configurations->get($this->getConfId().'formxml');

        $this->editItem = $item;
        $itemUid = ($this->editItem) ? $this->editItem->getUid() : 0;

        $this->form->init($this, $formXml, $itemUid, $configurations, $this->getConfId().'formconfig.');

        return $this->form;
    }

    /**
     * Modify user before update to db.
     *
     * @param array $params
     * @param tx_mkforms_forms_IForm $form
     */
    public function handleBeforeUpdateDB($params, $form)
    {
        $newPassword = $params['password123'];
        $feUser = tx_t3users_models_feuser::getCurrent();
        // If enableNonTcaColumns is set: do not eliminate the NonTCA-Enabled columns
        if (!$this->configurations->get($this->getConfId().'enableNonTcaColumns')) {
            // leeres Model bilden um Felder zu löschen die da nicht hingehören
            $params = \Sys25\RnBase\Backend\Utility\TCA::eleminateNonTcaColumns($feUser, $params);
        }
        // wenn die Option doubleoptin gewählt wurde dann werden die daten noch nicht
        // gespeichert sondern mit einem confirmstring per email verschickt und
        // erst bei der Bestätigung in die DB geschrieben
        if ($this->configurations->get($this->getConfId().'doubleoptin')) {
            // Zusätzlich Parameter setzen
            // Bestätigungscode generieren
            $params['confirmstring'] = md5(uniqid());
            $params['uid'] = $feUser->getUid();
            // username == mail setzen
            $params['username'] = $params['email'];
            // Mail schicken
            $emailService = tx_t3users_util_ServiceRegistry::getEmailService();
            $emailService->sendEditedData($feUser, $params, $this->configurations, $this->getConfId());
            // alles außer confirmstring löschen damit nur dieser in die db wandert
            $confirmString = $params['confirmstring'];
            unset($params);
            $params['confirmstring'] = $confirmString;
        } else {
            $params['tstamp'] = $GLOBALS['EXEC_TIME'];
            $params['name'] = trim($params['first_name'].' '.$params['last_name']);
            if ($newPassword) {
                $usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
                $params['password'] = $usrSrv->encryptPassword($newPassword);
            }
        }

        return $params;
    }

    /**
     * User is saved.
     *
     * @param array $params
     * @param tx_ameosformidable $form
     */
    public function handleUpdateDB($params, $form)
    {
        \Sys25\RnBase\Utility\Misc::callHook(
            't3users',
            'editFeUser_handleUpdateDB_hook',
            [
                'params' => &$params,
                'form' => &$form,
            ],
            $this
        );

        // Wohin soll umgeleitet werden?
        $redirect = $this->configurations->get($this->getConfId().'redirect.pid');
        $link = $this->configurations->createLink();
        $link->destination($redirect ? $redirect : $GLOBALS['TSFE']->id); // fallback
        $redirect_url = $link->makeUrl(false);
        header('Location: '.\Sys25\RnBase\Utility\Network::locationHeaderUrl($redirect_url));
    }

    public function getTemplateName()
    {
        return 'feuseredit';
    }

    public function getViewClassName()
    {
        return 'tx_t3users_views_EditFeUser';
    }
}

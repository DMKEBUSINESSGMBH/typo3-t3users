<?php
/**
 * @author René Nitzsche <dev@dmk-ebusiness.de>
 *
 *  Copyright notice
 *
 *  (c) 2013 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

/*
 * benötigte Klassen einbinden
 */

/**
 * Reset des Passworts eines Users.
 */
class tx_t3users_actions_ResetPassword extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{
    public function handleRequest(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $parameters = $request->getParameters();
        $viewdata = $request->getViewContext();

        $confirmstring = htmlspecialchars($parameters->get('confirm'));
        $uid = $parameters->getInt('uid');
        $viewdata->offsetSet('linkparams', ['confirm' => $confirmstring, 'uid' => $uid]);

        // Confirm prüfen
        $usrSrv = tx_t3users_util_ServiceRegistry::getFeUserService();
        $feuser = $usrSrv->getUserForConfirm($uid, $confirmstring);
        if (!$feuser) {
            $status = 'CONFIRMFAILED';
        } else {
            $status = 'FORM';
            $pass1 = htmlspecialchars($parameters->get('pass1'));
            $pass2 = htmlspecialchars($parameters->get('pass2'));

            if ($pass1) {
                $validated = ($pass1 && $pass1 == $pass2);
                $validationFailureMessage = '###LABEL_WRONG_PASS###';
                // Hook für weitere Validierungen
                \Sys25\RnBase\Utility\Misc::callHook(
                    't3users',
                    'resetPassword_ValidatePassword',
                    [
                        'validated' => &$validated,
                        'validationFailureMessage' => &$validationFailureMessage,
                        'password' => $pass1,
                    ],
                    $this
                );
                if ($validated) {
                    // Speichern
                    $usrSrv->saveNewPassword($feuser, $pass1);
                    // Und TODO: Redirect...
                    $status = 'FINISHED';
                } else {
                    // Validierung fehlgeschlagen
                    $viewdata->offsetSet('message', $validationFailureMessage);
                }
            }
        }
        $viewdata->offsetSet('subpart', $status);

        return '';
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return 'resetpassword';
    }

    /**
     * @return string
     */
    public function getViewClassName()
    {
        return 'tx_t3users_views_ResetPassword';
    }
}

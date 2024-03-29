<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2013 Rene Nitzsche (dev@dmk-ebusiness.de)
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
 * Service to extend login form.
 *
 * @author Rene Nitzsche
 */
class tx_t3users_services_LoginForm extends \TYPO3\CMS\Core\Service\AbstractService
{
    /**
     * Aufgabe ist es, das Login-Formular so zu erweitern, daß es erfolgreich abgeschickt wird.
     * Dazu muss eine onSubmit Funktion geliefert werden. Zusätzlich sind für die Verschlüsselung
     * der Zugangsdaten ggf. weitere LoginFelder und zusätzlicher JS-Code notwendig.
     *
     * @param stdClass $code
     * @param tx_t3users_actions_Login $plugin
     *
     * @return array
     *
     * @todo rsaauth exists no longer. Is there anything to extend at all? If not remove everything about the
     * extending the form.
     */
    public function extendLoginForm($code, $statusKey, $configurations, $confId, $plugin)
    {
        $method = 'default';
        $data = $configurations->get($confId.'extend.'.$method.'.');
        $keys = ['formFields', 'jsFiles', 'jsCode', 'onsubmit'];
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $code->$key = $data[$key];
            }
        }
    }
}

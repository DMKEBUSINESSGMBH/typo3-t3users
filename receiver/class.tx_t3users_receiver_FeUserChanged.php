<?php

use Sys25\RnBase\Utility\Strings;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Rene Nitzsche (dev@dmk-ebusiness.de)
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
 * Implementierung für einen Mailempfänger vom Typ FeUser.
 * Ändert ein User seine Email dann wird beim bestätigen
 * die bereits bestätigte Mail erneut an die geänderten Daten geschickt (
 * liegt am mail log da die mail dort für die alte aber nicht die
 * geänderte adresse drin steht aber der receiver der mail nun eine
 * neue adresse hat. ergo wird die mail nochmals verschickt.
 * also brauchen wir einen eigen receiver der immer die ursprüngliche
 * adresse zurück gibt statt die aktuelle des users.
 */
class tx_t3users_receiver_FeUserChanged extends tx_mkmailer_receiver_FeUser
{
    public function getValueString()
    {
        return is_object($this->obj) ? $this->obj->getUid().','.(!empty($this->email) ? $this->email : $this->obj->getProperty('email')) : '';
    }

    public function setValueString($value)
    {
        $aValues = Strings::trimExplode(',', $value, true);
        $this->setFeUser(tx_t3users_models_feuser::getInstance(intval($aValues[0])));
        //die neue, geänderte Email Adresse im Empfänger setzen damit die
        //Mail nicht 2 mal verschickt wird an die neue Adresse
        $this->email = $aValues[1];
    }

    protected function getEmail()
    {
        if (empty($this->email)) {
            return false;
        }
        //else
        return $this->email;
    }
}

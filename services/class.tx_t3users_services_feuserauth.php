<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche (dev@dmk-ebusiness.de)
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

tx_rnbase::load('tx_rnbase_configurations');
tx_rnbase::load('Tx_Rnbase_Service_Authentication');
tx_rnbase::load('Tx_Rnbase_Utility_Strings');

/**
 * Service for accessing user information
 * This service enables authentication by username and email
 *
 * @author Rene Nitzsche
 */
class tx_t3users_services_feuserauth extends Tx_Rnbase_Service_Authentication
{
    public function initAuth($subType, $loginData, $authInfo, $userauth)
    {
        parent::initAuth($subType, $loginData, $authInfo, $userauth);

        if (intval(tx_rnbase_configurations::getExtensionCfgValue('t3users', 'enableLoginByEmail')) &&
                    Tx_Rnbase_Utility_Strings::validEmail($loginData['uname'])) {
            $this->pObj->username_column = 'email';
            $this->db_user['username_column'] = 'email';
        }
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/services/class.tx_t3users_services_feuserauth.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/services/class.tx_t3users_services_feuserauth.php']);
}

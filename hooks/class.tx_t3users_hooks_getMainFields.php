<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche <dev@dmk-ebusiness.de>
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
 * Prepare field before output in tce form.
 */
class tx_t3users_hooks_getMainFields
{
    public function getMainFields_preProcess($table, &$row, $tceform)
    {
        if ('fe_users' == $table) {
            if (!strstr($row['uid'], 'NEW')) {
                tx_rnbase::load('tx_rnbase_util_DB');
                $row['birthday'] = ('1' == $GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat']) ?
                                    tx_rnbase_util_DB::date_mysql2mdY($row['birthday']) :
                                    tx_rnbase_util_DB::date_mysql2dmY($row['birthday']);
            }
        }
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/hooks/class.tx_t3users_hooks_getMainFields.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/hooks/class.tx_t3users_hooks_getMainFields.php'];
}

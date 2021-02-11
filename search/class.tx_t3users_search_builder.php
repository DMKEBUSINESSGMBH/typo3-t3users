<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Rene Nitzsche
 *  Contact: dev@dmk-ebusiness.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

tx_rnbase::load('tx_rnbase_util_Strings');
tx_rnbase::load('tx_rnbase_util_SearchBase');

/**
 * Mit dem Builder werden haufig auftretende Suchanfragen zusammengebaut.
 *
 * @author Rene Nitzsche
 */
class tx_t3users_search_builder
{
    /**
     * Search for feuser by email.
     *
     * @param array $fields
     * @param string $teamUids comma separated list of team UIDs
     *
     * @return bool true if condition is set
     */
    public static function buildFeuserByEmail(&$fields, $email, $pids = '')
    {
        $result = false;
        if (strlen(trim($email))) {
            $fields['FEUSER.EMAIL'][OP_EQ_NOCASE] = $email;
            $result = true;
            $result = true;
        }
        if (strlen(trim($pids))) {
            $pids = implode(',', tx_rnbase_util_Strings::intExplode(',', $pids));
            $joined['value'] = $pids;
            $joined['cols'] = ['FEUSER.PID'];
            $joined['operator'] = OP_INSET_INT;
            $fields[SEARCH_FIELD_JOINED][] = $joined;
            $result = true;
        }

        return $result;
    }

    /**
     * Freetext search for feusers.
     *
     * @param array $fields
     * @param string $searchword
     */
    public static function buildFeUserFreeText(&$fields, $searchword)
    {
        $result = false;
        if (strlen(trim($searchword))) {
            $joined['value'] = trim($searchword);
            $joined['cols'] = ['FEUSER.NAME', 'FEUSER.USERNAME'];
            $joined['operator'] = OP_LIKE;
            $fields[SEARCH_FIELD_JOINED][] = $joined;
            $result = true;
        }

        return $result;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/search/class.tx_t3users_search_builder.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/search/class.tx_t3users_search_builder.php'];
}

<?php
/***************************************************************
 *  Copyright notice
*
*  (c) 2008 Rene Nitzsche
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

/**
 * Class to search feuser from database.
 *
 * @author Rene Nitzsche
 */
class tx_t3users_search_feuser extends \Sys25\RnBase\Search\SearchBase
{
    protected function getTableMappings()
    {
        $tableMapping['FEUSER'] = 'fe_users';
        $tableMapping['FEGROUP'] = 'fe_groups';
        $tableMapping['FESESSION'] = 'fe_sessions';
        // Hook to append other tables
        \Sys25\RnBase\Utility\Misc::callHook(
            't3users',
            'search_feuser_getTableMapping_hook',
            ['tableMapping' => &$tableMapping],
            $this
        );

        return $tableMapping;
    }

    protected function useAlias()
    {
        return false;
    }

    protected function getBaseTableAlias()
    {
        return 'FEUSER';
    }

    protected function getBaseTable()
    {
        return 'fe_users';
    }

    public function getWrapperClass()
    {
        return 'tx_t3users_models_feuser';
    }

    protected function getJoins($tableAliases)
    {
        $join = '';
        if (isset($tableAliases['FEGROUP'])) {
            $join .= ($this->useAlias()) ?
            ' JOIN fe_groups AS FEGROUP ON FIND_IN_SET( FEGROUP.uid, FEUSER.usergroup )' :
            ' JOIN fe_groups ON FIND_IN_SET( fe_groups.uid, fe_users.usergroup )';
        }
        if (isset($tableAliases['FESESSION'])) {
            $join .= ($this->useAlias()) ?
            ' JOIN fe_sessions AS FESESSION ON (FESESSION.ses_userid = FEUSER.uid)' :
            ' JOIN fe_sessions ON (ses_userid = uid)';
        }
        // Hook to append other tables
        \Sys25\RnBase\Utility\Misc::callHook(
            't3users',
            'search_feuser_getJoins_hook',
            ['join' => &$join, 'tableAliases' => $tableAliases],
            $this
        );

        return $join;
    }
}

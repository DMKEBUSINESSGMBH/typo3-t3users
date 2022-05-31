<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Rene Nitzsche
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
 * Class to search logging records from database.
 *
 * @author Rene Nitzsche
 */
class tx_t3users_search_log extends \Sys25\RnBase\Search\SearchBase
{
    protected function getTableMappings()
    {
        $tableMapping['LOG'] = 'tx_t3users_log';
        $tableMapping['FEUSER'] = 'fe_users';
        // Hook to append other tables
        \Sys25\RnBase\Utility\Misc::callHook(
            't3users',
            'search_log_getTableMapping_hook',
            ['tableMapping' => &$tableMapping],
            $this
        );

        return $tableMapping;
    }

    protected function useAliases()
    {
        return true;
    }

    protected function getBaseTable()
    {
        return 'tx_t3users_log';
    }

    public function getWrapperClass()
    {
        return 'tx_t3users_models_log';
    }

    protected function getJoins($tableAliases)
    {
        $join = '';
        if (isset($tableAliases['FEUSER'])) {
            $join .= ' JOIN fe_users AS FEUSER ON fe_users.uid = LOG.fe_user';
        }
        // Hook to append other tables
        \Sys25\RnBase\Utility\Misc::callHook(
            't3users',
            'search_log_getJoins_hook',
            ['join' => &$join, 'tableAliases' => $tableAliases],
            $this
        );

        return $join;
    }
}

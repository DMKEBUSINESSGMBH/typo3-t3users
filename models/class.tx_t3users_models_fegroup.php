<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche (dev@dmk-ebusiness.de)
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

tx_rnbase::load('tx_rnbase_model_base');

/**
 * Model for fe_group.
 */
class tx_t3users_models_fegroup extends tx_rnbase_model_base
{
    private static $instances = [];

    public function getTableName()
    {
        return 'fe_groups';
    }

    /**
     * Liefert die Instance mit der übergebenen UID. Die Daten werden gecached, so daß
     * bei zwei Anfragen für die selbe UID nur ein DB Zugriff erfolgt.
     *
     * @param int $uid
     *
     * @return tx_t3users_models_feuser
     */
    public static function getInstance($data = null)
    {
        $uid = (int) $data;
        if (!$uid) {
            throw new Exception('No uid for fe_group given!');
        }
        if (!is_object(self::$instances[$uid])) {
            self::$instances[$uid] = new tx_t3users_models_fegroup($uid);
        }

        return self::$instances[$uid];
    }

    /**
     * Returns all users of this group.
     *
     * @return array[tx_t3users_models_feuser]
     */
    public function getUsers()
    {
        $srv = tx_t3users_util_ServiceRegistry::getFeUserService();

        return $srv->getFeUser($this->getUid());
    }

    /**
     * Returns the group name.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getProperty('title');
    }
}

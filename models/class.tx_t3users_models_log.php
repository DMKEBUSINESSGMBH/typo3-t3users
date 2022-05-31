<?php
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

interface tx_t3users_models_ILog
{
    /**
     * Returns the uid of current feuser.
     *
     * @return int
     */
    public function getFEUserUid();

    /**
     * Returns the type of action. This is a unique type string.
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the uid of a used record.
     *
     * @return int
     */
    public function getRecUid();

    /**
     * Returns the uid of the used records table.
     *
     * @return string
     */
    public function getRecTable();

    /**
     * Optional additional data string.
     *
     * @return string or array
     */
    public function getData();

    /**
     * Optional timestamp.
     *
     * @return string Format: Y-m-d H:i:s
     */
    public function getTimeStamp();
}

/**
 * Model for fe_user.
 */
class tx_t3users_models_log extends \Sys25\RnBase\Domain\Model\BaseModel implements tx_t3users_models_ILog
{
    public function getTableName()
    {
        return 'tx_t3users_log';
    }

    public function getFEUserUid()
    {
        return $this->getProperty('feuser');
    }

    /**
     * Liefert den FEUser.
     *
     * @return tx_t3users_models_feuser
     */
    public function getFEUser()
    {
        return tx_t3users_models_feuser::getInstance($this->getProperty('feuser'));
    }

    /**
     * Returns the type of action. This is a unique type string.
     *
     * @return string
     */
    public function getType()
    {
        return $this->getProperty('typ');
    }

    public function getRecUid()
    {
        return (int) $this->getProperty('recuid');
    }

    public function getRecTable()
    {
        return (string) $this->getProperty('rectable');
    }

    public function getData()
    {
        return $this->getProperty('data');
    }

    /**
     * Optional timestamp.
     *
     * @return string Format: Y-m-d H:i:s
     */
    public function getTimeStamp()
    {
        return $this->getProperty('tstamp');
    }
}

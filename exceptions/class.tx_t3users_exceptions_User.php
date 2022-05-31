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

/**
 * Exception used for problems with feusers.
 */
class tx_t3users_exceptions_User extends Exception
{
    protected $feuser;
    protected $parent;

    public function __construct($message, &$feuser = null, $parent = null)
    {
        parent::__construct($message, 0);
        $this->feuser = $feuser;
        $this->parent = $parent;
    }
}

<?php
/**
 *  Copyright notice.
 *
 *  (c) 2011 René Nitzsche <dev@dmk-ebusiness.de>
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
 */

/**
 * Backend Modul für t3users.
 *
 * @author René Nitzsche
 */
class tx_t3users_mod_Module extends \Sys25\RnBase\Backend\Module\BaseModule
{
    /**
     * Method to get the extension key.
     *
     * @return  string Extension key
     */
    public function getExtensionKey()
    {
        return 't3users';
    }

    protected function getFormTag()
    {
        $modUrl = \Sys25\RnBase\Backend\Utility\BackendUtility::getModuleUrl(
            'web_T3usersBackend',
            ['id' => $this->getPid()],
            ''
        );

        return '<form action="'.$modUrl.'" method="POST" name="editform" id="editform">';
    }
}

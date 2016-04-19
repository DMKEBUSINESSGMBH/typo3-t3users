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


tx_rnbase::load('tx_t3users_mod_decorator_Base');


/**
 * Diese Klasse ist für die Darstellung von FEUsern im Backend verantwortlich
 */
class tx_t3users_util_FEUserDecorator {

	public function format($value, $colName, $record, $entry, $formTool) {
		$ret = $value;
		switch ($colName) {
			case 'date':
				$ret = date('H:i d.m.y', $value);
				break;
			case 'usergroup':
				$ret = tx_t3users_mod_decorator_Base::showUsergroups($entry, $formTool);
				break;
			case 'username':
				$ret = $entry->isDisabled() ? '<s>'.$value.'</s>' : $value;
				$ret .= ' ' . $formTool->createEditLink('fe_users', $record['uid'], '');
				if($entry->getEmail()) {
					global $LANG;
					$ret .= '<br />'.$LANG->getLL('label_email').': <a href="mailto:' . $entry->getEmail() .'">'.$entry->getEmail().'</a>';
				}
				break;
		}
		return $ret;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/util/class.tx_t3users_util_FEUserDecorator.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/util/class.tx_t3users_util_FEUserDecorator.php']);
}
?>
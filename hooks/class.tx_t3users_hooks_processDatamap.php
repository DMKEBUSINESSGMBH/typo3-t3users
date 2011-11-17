<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche <rene@system25.de>
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
 * Dieser Hook wird beim Speichern der Werte in die Datenbank aufgerufen.
 *
 */
class tx_t3users_hooks_processDatamap {

	/**
	 *
	 * @param string $status
	 * @param string $table
	 * @param int $id
	 * @param array $fieldArray
	 * @param tce_main $tce
	 */
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$tce) {
		/* If we have an existing calendar event */
		if ($table == 'fe_users' && count($fieldArray)>1) {
			if($fieldArray['birthday']){
				$fieldArray['birthday'] = $this->convertBackendDateToYMD($fieldArray['birthday']);
			}
		}
	}
  /**
   * Miniulation der Daten vor dem Speichern in die DB
   */
  function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$tcemain)  {

  }

	/**
	 * Converts a date from the backend (m-d-Y or d-m-Y) into the Y-m-d format.
	 * from ext cal
	 * @param		string		The date to convert.
	 * @return		string		The date in Ymd format.
	 */
	function convertBackendDateToYMD($dateString) {
		$dateArray = explode('-',$dateString);
		$ymdString = ($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] == '1') ? 
									$dateArray[2].'-'.$dateArray[0].'-'.$dateArray[1] : $dateArray[2].'-'.$dateArray[1].'-'.$dateArray[0];

		return $ymdString;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/hooks/class.tx_t3users_hooks_processDatamap.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/hooks/class.tx_t3users_hooks_processDatamap.php']);
}

?>
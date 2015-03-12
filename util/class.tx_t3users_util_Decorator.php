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
 * Die Klasse bereitet Objekte für die Darstellung im Backend auf
 */
class tx_t3users_util_Decorator {

	static function prepareTable($entries, $columns, $formTool, $options) {
		$arr = Array( 0 => Array( self::getHeadline($parts, $columns, $options) ));
		foreach($entries As $entry){
			$record = is_object($entry) ? $entry->record : $entry;
			$row = array();
			if(isset($options['checkbox'])) {
				$checkName = isset($options['checkboxname']) ? $options['checkboxname'] : 'checkEntry';
				// Check if entry is checkable
				if(!is_array($options['dontcheck']) || !array_key_exists($record['uid'], $options['dontcheck']))
					$row[] = $formTool->createCheckbox($checkName.'[]', $record['uid']);
				else
					$row[] = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/zoom2.gif','width="11" height="12"').' title="Info: '. $options['dontcheck'][$record['uid']] .'" border="0" alt="" />';
			}
			reset($columns);
			foreach($columns As $column => $data) {
				// Hier erfolgt die Ausgabe der Daten für die Tabelle. Wenn eine method angegeben
				// wurde, dann muss das Entry als Objekt vorliegen. Es wird dann die entsprechende
				// Methode aufgerufen. Es kann auch ein Decorator-Objekt gesetzt werden. Dann wird
				// von diesem die Methode format aufgerufen und der Wert, sowie der Name der aktuellen
				// Spalte übergeben. Ist nichts gesetzt wird einfach der aktuelle Wert verwendet.
				if(isset($data['method'])) {
					$row[] = call_user_func(array($entry, $data['method']));
				}
				elseif(isset($data['decorator'])) {
					$decor = $data['decorator'];
					$row[] = $decor->format($record[$column],$column, $record, $entry, $formTool);
				}
				else {
					$row[] = $record[$column];
				}
			}
			if(isset($options['linker']))
				$row[] = self::addLinker($options, $entry, $formTool);
			$arr[0][] = $row;
		}
		return $arr;
	}

	/**
	 * Liefert die passenden Überschrift für die Tabelle
	 *
	 * @param int $parts
	 * @return array
	 */
	static function getHeadline($parts, $columns, $options) {
		global $LANG;
		$arr = array();
		if(isset($options['checkbox'])) {
			$arr[] = '&nbsp;'; // Spalte für Checkbox
		}
		$tableName = isset($options['tablename']) ? $options['tablename'] : '';
		foreach($columns As $column => $data) {
			if(intval($data['nocolumn'])) continue;
			$arr[] = intval($data['notitle']) ? '' :
					$LANG->getLL((isset($data['title']) ? $data['title'] : $tableName.'_' . $column));
		}
		if(isset($options['linker']))
			$arr[] = $LANG->getLL('label_action');
		return $arr;
	}

	static function addLinker($options, $obj, $formTool) {
		$out = '';
		if(isset($options['linker'])) {
			$linkerArr = $options['linker'];
			if(is_array($linkerArr) && count($linkerArr)) {
				$currentPid = intval($options['pid']);
				foreach($linkerArr As $linker) {
					$out .= $linker->makeLink($obj, $formTool, $currentPid, $options);
					$out .= $options['linkerimplode'] ? $options['linkerimplode'] : '<br />';
				}
			}
		}
		return $out;
	}
}

interface tx_t3users_util_Linker {
	function makeLink($obj, $formTool, $currentPid, $options);
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/util/class.tx_t3users_util_Decorator.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/util/class.tx_t3users_util_Decorator.php']);
}


?>

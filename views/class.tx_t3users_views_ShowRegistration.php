<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');


tx_rnbase::load('tx_rnbase_view_Base');


/**
 * Viewklasse für die Anzeige
 */
class tx_t3users_views_ShowRegistration extends tx_rnbase_view_Base {
	/**
	 * Erstellen des Frontend-Outputs
	 */
	function createOutput($template, &$viewData, &$configurations, &$formatter){
		$editors =& $viewData->offsetGet('editors');
		$subpartName = '###PART_'.$viewData->offsetGet('part').'###';
		$template = $formatter->cObj->getSubpart($template, $subpartName);
    
    $out = '';

    // Jetzt die Editoren einbinden
    foreach($editors AS $marker => $editor) {
    	$markerArray['###'.$marker.'###'] = $editor;
    }
    $out = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
    return $out;
  }
  function getMainSubpart() {return '###REGISTRATION###';}
  
}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_ShowRegistration.php'])
{
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_ShowRegistration.php']);
}
?>
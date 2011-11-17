<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_util_Templates');

/**
 * Viewklasse für die Anzeige
 */
class tx_t3users_views_ShowRegistrationConfirm extends tx_rnbase_view_Base {
	/**
	 * Erstellen des Frontend-Outputs
	 * @param string $template
	 * @param ArrayObject $viewData
	 */
	function createOutput($template, &$viewData, &$configurations, &$formatter) {
		$subpartName = '###PART_'.$viewData->offsetGet('part').'###';
		$template = $formatter->cObj->getSubpart($template, $subpartName);

		if(tx_rnbase_util_BaseMarker::containsMarker($template, 'FEUSER_')) {
			$marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');
	    $template = $marker->parseTemplate($template, $viewData->offsetGet('feuser'), $formatter, $this->getController()->getConfId().'feuser.', 'FEUSER');
		}

    $out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray);
    return $out;
  }
  function getMainSubpart() {return '###REGISTRATIONCONFIRM###';}
  
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_ShowRegistrationConfirm.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_ShowRegistrationConfirm.php']);
}
?>
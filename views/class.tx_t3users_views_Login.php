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




tx_rnbase::load('tx_rnbase_view_Base');
tx_rnbase::load('tx_rnbase_util_BaseMarker');


/**
 * Viewklasse für die Darstellung der Loginbox
 */
class tx_t3users_views_Login extends tx_rnbase_view_Base {

	/**
	 * Enter description here...
	 *
	 * @param string $template
	 * @param arrayobject $viewData
	 * @param tx_rnbase_configurations $configurations
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @return string
	 */
	function createOutput($template, &$viewData, &$configurations, &$formatter) {
		// Wir holen die Daten von der Action ab
		$feuser = $viewData->offsetGet('feuser');
//		$subpart = $viewData->offsetGet('subpart');
		$markers = $viewData->offsetGet('markers');
//		$subTemplate = $formatter->cObj->getSubpart($template,$subpart);
		$markerArray = $formatter->getItemMarkerArrayWrapped($markers, 'loginbox.marker.' , 0, '');
		// Passwort-Link
		tx_rnbase_util_BaseMarker::initLink($markerArray, $subpartArray,
							$wrappedSubpartArray, $formatter,
							'loginbox.', 'forgotpass', 'LOGINBOX', array('NK_forgotpass' => '1'));
		// Register-Link
		tx_rnbase_util_BaseMarker::initLink($markerArray, $subpartArray,
							$wrappedSubpartArray, $formatter,
							'loginbox.', 'register', 'LOGINBOX', array());
							
		$out = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
		// We do it twice, since some marker contain other markers
		$out = $formatter->cObj->substituteMarkerArrayCached($out, $markerArray, $subpartArray, $wrappedSubpartArray);
		
    if(is_object($feuser)) {
	    // Jetzt mit dem FEuser-Marker drüber
			$marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');
			$out = $marker->parseTemplate($out, $feuser, $formatter, 'loginbox.feuser.');
    }
    
		return $out;
	}

  /**
   * Subpart der im HTML-Template geladen werden soll. Dieser wird der Methode
   * createOutput automatisch als $template übergeben.
   *
   * @return string
   */
  function getMainSubpart(&$viewData) {
  	return $viewData->offsetGet('subpart');
//  	return '###LOGINBOX###';
  }
  


}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_Login.php'])
{
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_Login.php']);
}
?>
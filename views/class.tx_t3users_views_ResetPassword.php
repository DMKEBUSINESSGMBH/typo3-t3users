<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2014 Rene Nitzsche (dev@dmk-ebusiness.de)
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
tx_rnbase::load('tx_rnbase_util_Templates');



/**
 * Viewklasse für die Darstellung der Loginbox
 */
class tx_t3users_views_ResetPassword extends tx_rnbase_view_Base
{

    /**
     * Enter description here...
     *
     * @param string $template
     * @param arrayobject $viewData
     * @param tx_rnbase_configurations $configurations
     * @param tx_rnbase_util_FormatUtil $formatter
     * @return string
     */
    public function createOutput($template, &$viewData, &$configurations, &$formatter)
    {
        // Wir holen die Daten von der Action ab
        $feuser = $viewData->offsetGet('feuser');
        $subpart = $viewData->offsetGet('subpart');
        $linkParams = $viewData->offsetGet('linkparams');

        $markers = array();
        $markers['message'] = $viewData->offsetGet('message');
        $markerArray = $formatter->getItemMarkerArrayWrapped($markers, $this->getController()->getConfId().'marker.', 0, '');

        $markerArray['###ACTION_URI###'] = $this->createPageUri($configurations, $linkParams);
        $out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        if (is_object($feuser)) {
            // Jetzt mit dem FEuser-Marker drüber
            $marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');
            $out = $marker->parseTemplate($out, $feuser, $formatter, 'loginbox.feuser.');
        }

        return $out;
    }

    /**
     *
     * @param tx_rnbase_configurations $configurations
     */
    protected function createPageUri($configurations, $params = array())
    {
        $link = $configurations->createLink();
        $link->initByTS($configurations, $this->getController()->getConfId().'formUrl.', $params);

        return $link->makeUrl(false);
    }

    /**
     * Subpart der im HTML-Template geladen werden soll. Dieser wird der Methode
     * createOutput automatisch als $template übergeben.
     *
     * @return string
     */
    public function getMainSubpart(&$viewData)
    {
        $subpart = $viewData->offsetGet('subpart');

        return $subpart ? '###RESETPASSWORD_'.strtoupper($subpart).'###' : '###RESETPASSWORD_FORM###';
    }
}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_ResetPassword.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_ResetPassword.php']);
}

<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche (dev@dmk-ebusiness.de)
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


if (tx_rnbase_util_Extensions::isLoaded('dam')) {
    require_once(tx_rnbase_util_Extensions::extPath('dam', 'lib/class.tx_dam_media.php'));
}

tx_rnbase::load('tx_rnbase_view_Base');


/**
 * Viewclass to show a user.
 */
class tx_t3users_views_EditFeUser extends tx_rnbase_view_Base
{
    /**
     * Erstellen des Frontend-Outputs
     */
    public function createOutput($template, &$viewData, &$configurations, &$formatter)
    {
        $form =& $viewData->offsetGet('form');
        $markerArray = array();
        $markerArray['###FORM###'] = $form;
        $template = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray);

        // Jetzt nochmal den User rendern
        $feuser =& $viewData->offsetGet('user');
        $marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');

        $confId = 'feuseredit.feuser.';
        $out = $marker->parseTemplate($template, $feuser, $formatter, $confId);

        $params['confid'] = $confId;
        $params['marker'] = 'FEUSER';
        $params['feuser'] = $feuser;
        tx_rnbase_util_BaseMarker::callModules($template, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $formatter);
        $out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $out;
    }


    /**
     * Returns the subpart to use for in template
     *
     * @return string
     */
    public function getMainSubpart(&$viewData)
    {
        return '###FEUSER_EDIT###';
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_EditFeUser.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_EditFeUser.php']);
}

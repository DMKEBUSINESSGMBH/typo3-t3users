<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (dev@dmk-ebusiness.de)
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
tx_rnbase::load('tx_rnbase_util_Templates');

/**
 * Viewklasse für die Anzeige.
 */
class tx_t3users_views_ShowRegistrationConfirm extends tx_rnbase_view_Base
{
    /**
     * Erstellen des Frontend-Outputs.
     *
     * @param string $template
     * @param ArrayObject $viewData
     * @param \Sys25\RnBase\Configuration\ProcessorInterface $configurations
     * @param tx_rnbase_util_FormatUtil $formatter
     *
     * @return string
     */
    public function createOutput($template, &$viewData, &$configurations, &$formatter)
    {
        $subpartName = '###PART_'.$viewData->offsetGet('part').'###';
        $template = tx_rnbase_util_Templates::getSubpart($template, $subpartName);

        if (tx_rnbase_util_BaseMarker::containsMarker($template, 'FEUSER_')) {
            $marker = tx_rnbase::makeInstance('tx_t3users_util_FeUserMarker');
            $template = $marker->parseTemplate($template, $viewData->offsetGet('feuser'), $formatter, $this->getController()->getConfId().'feuser.', 'FEUSER');
        }

        return $template;
    }

    /**
     * Returns the subpart to use for in template.
     *
     * @return string
     */
    public function getMainSubpart(&$viewData)
    {
        return '###REGISTRATIONCONFIRM###';
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_ShowRegistrationConfirm.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/views/class.tx_t3users_views_ShowRegistrationConfirm.php'];
}

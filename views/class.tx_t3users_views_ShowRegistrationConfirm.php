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

/**
 * Viewklasse für die Anzeige.
 */
class tx_t3users_views_ShowRegistrationConfirm extends \Sys25\RnBase\Frontend\View\Marker\BaseView
{
    protected function createOutput($template, \Sys25\RnBase\Frontend\Request\RequestInterface $request, $formatter)
    {
        $subpartName = '###PART_'.$request->getViewContext()->offsetGet('part').'###';
        $template = \Sys25\RnBase\Frontend\Marker\Templates::getSubpart($template, $subpartName);

        if (\Sys25\RnBase\Frontend\Marker\BaseMarker::containsMarker($template, 'FEUSER_')) {
            $marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_util_FeUserMarker');
            $feuser = $request->getViewContext()->offsetGet('feuser');
            $template = $marker->parseTemplate($template, $feuser, $formatter, $request->getConfId().'feuser.', 'FEUSER');
        }

        return $template;
    }

    public function getMainSubpart(\Sys25\RnBase\Frontend\View\ContextInterface $viewData)
    {
        return '###REGISTRATIONCONFIRM###';
    }
}

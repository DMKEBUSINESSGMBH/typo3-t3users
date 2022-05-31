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

/**
 * Viewclass to show a user.
 */
class tx_t3users_views_EditFeUser extends \Sys25\RnBase\Frontend\View\Marker\BaseView
{
    protected function createOutput($template, \Sys25\RnBase\Frontend\Request\RequestInterface $request, $formatter)
    {
        $viewData = $request->getViewContext();

        $form = $viewData->offsetGet('form');
        $markerArray = [];
        $markerArray['###FORM###'] = $form;
        $template = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($template, $markerArray);

        // Jetzt nochmal den User rendern
        $feuser = $viewData->offsetGet('user');
        $marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_util_FeUserMarker');

        $confId = 'feuseredit.feuser.';
        $template = $marker->parseTemplate($template, $feuser, $formatter, $confId);

        $params['confid'] = $confId;
        $params['marker'] = 'FEUSER';
        $params['feuser'] = $feuser;
        $subpartArray = $wrappedSubpartArray = [];
        \Sys25\RnBase\Frontend\Marker\BaseMarker::callModules($template, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $formatter);
        $out = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $out;
    }

    public function getMainSubpart(\Sys25\RnBase\Frontend\View\ContextInterface $viewData)
    {
        return '###FEUSER_EDIT###';
    }
}

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

/**
 * Viewklasse für die Darstellung der Loginbox.
 */
class tx_t3users_views_ResetPassword extends \Sys25\RnBase\Frontend\View\Marker\BaseView
{
    protected function createOutput($template, \Sys25\RnBase\Frontend\Request\RequestInterface $request, $formatter)
    {
        $viewData = $request->getViewContext();
        // Wir holen die Daten von der Action ab
        $feuser = $viewData->offsetGet('feuser');
        $linkParams = $viewData->offsetGet('linkparams');

        $markers = [];
        $markers['message'] = $viewData->offsetGet('message');
        $markerArray = $formatter->getItemMarkerArrayWrapped($markers, $request->getConfId().'marker.', 0, '');

        $markerArray['###ACTION_URI###'] = $this->createPageUri($request->getConfigurations(), $linkParams);
        $subpartArray = $wrappedSubpartArray = [];
        $out = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        if (is_object($feuser)) {
            // Jetzt mit dem FEuser-Marker drüber
            $marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_util_FeUserMarker');
            $out = $marker->parseTemplate($out, $feuser, $formatter, 'loginbox.feuser.');
        }

        return $out;
    }

    /**
     * @param \Sys25\RnBase\Configuration\Processor $configurations
     */
    protected function createPageUri($configurations, $params = [])
    {
        $link = $configurations->createLink();
        $link->initByTS($configurations, $this->getController()->getConfId().'formUrl.', $params);

        return $link->makeUrl(false);
    }

    public function getMainSubpart(\Sys25\RnBase\Frontend\View\ContextInterface $viewData)
    {
        $subpart = $viewData->offsetGet('subpart');

        return $subpart ? '###RESETPASSWORD_'.strtoupper($subpart).'###' : '###RESETPASSWORD_FORM###';
    }
}

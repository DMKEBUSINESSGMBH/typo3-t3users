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

/**
 * Viewklasse für die Darstellung der Loginbox.
 */
class tx_t3users_views_Login extends \Sys25\RnBase\Frontend\View\Marker\BaseView
{
    protected function createOutput($template, \Sys25\RnBase\Frontend\Request\RequestInterface $request, $formatter)
    {
        $viewData = $request->getViewContext();

        // Wir holen die Daten von der Action ab
        $feuser = $viewData->offsetExists('feuser') ? $viewData->offsetGet('feuser') : null;
        $markers = $viewData->offsetExists('markers') ? $viewData->offsetGet('markers') : null;
        $markerArray = $formatter->getItemMarkerArrayWrapped($markers, 'loginbox.marker.', 0, '');
        $subpartArray = $wrappedSubpartArray = [];
        // Passwort-Link
        \Sys25\RnBase\Frontend\Marker\BaseMarker::initLink(
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray,
            $formatter,
            'loginbox.',
            'forgotpass',
            'LOGINBOX',
            ['NK_forgotpass' => '1']
        );
        // Register-Link
        \Sys25\RnBase\Frontend\Marker\BaseMarker::initLink(
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray,
            $formatter,
            'loginbox.',
            'register',
            'LOGINBOX',
            []
        );

        $out = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
        // We do it twice, since some marker contain other markers
        $out = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($out, $markerArray, $subpartArray, $wrappedSubpartArray);

        if (is_object($feuser)) {
            // Jetzt mit dem FEuser-Marker drüber
            $marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_util_FeUserMarker');
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
    public function getMainSubpart(\Sys25\RnBase\Frontend\View\ContextInterface $viewData)
    {
        return $viewData->offsetGet('subpart');
    }
}

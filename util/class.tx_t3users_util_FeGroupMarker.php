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
 * Diese Klasse ist für die Erstellung von Markerarrays für FE Group verantwortlich.
 */
class tx_t3users_util_FeGroupMarker extends tx_rnbase_util_BaseMarker
{
    /**
     * Initialisiert den Marker Array.
     * Optionen:
     * - hideregistrations
     * - hideuploads.
     *
     * @param array $options Hinweise an den Marker
     */
    public function __construct($options = false)
    {
        $this->options = is_array($options) ? $options : [];
    }

    /**
     * Initialisiert die Labels für die Profile-Klasse.
     *
     * @param tx_rnbase_util_FormatUtil $formatter
     * @param array $defaultMarkerArr
     */
    public function initLabelMarkers(&$formatter, $confId, $defaultMarkerArr = 0, $marker = 'FEGROUP')
    {
        return $this->prepareLabelMarkers('tx_t3users_models_fegroup', $formatter, $confId, $defaultMarkerArr, $marker);
    }

    /**
     * @param string $template das HTML-Template
     * @param tx_t3users_models_fegroup $fegroup The fe group
     * @param $formatter der zu verwendente Formatter
     * @param string $confId Pfad der TS-Config des Objekt, z.B. 'listView.event.'
     * @param $marker Name des Markers für ein Object, z.B. FEUSER
     *        Von diesem String hängen die entsprechenden weiteren Marker ab: ###FEGROUP_TITLE###
     *
     * @return string das geparste Template
     */
    public function parseTemplate($template, &$fegroup, &$formatter, $confId, $marker = 'FEGROUP')
    {
        if (!is_object($fegroup)) {
            return '<!-- -->';
        }
        $markerArray = $formatter->getItemMarkerArrayWrapped($fegroup->getProperty(), $confId, 0, $marker.'_', $fegroup->getColumnNames());
        $wrappedSubpartArray = [];
        $subpartArray = [];

        $out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $out;
    }
}

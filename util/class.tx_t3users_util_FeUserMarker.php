<?php

use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\TYPO3;

/***************************************************************
 * Copyright notice
 *
 * (c) 2008-2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Repository to handle companies.
 *
 * @author Rene Nitzscher
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_t3users_util_FeUserMarker extends \Sys25\RnBase\Frontend\Marker\SimpleMarker
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
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil $formatter
     * @param string $confId
     * @param mixed|array $defaultMarkerArr
     * @param string $marker
     *
     * @return array
     */
    public function initLabelMarkers(\Sys25\RnBase\Frontend\Marker\FormatUtil $formatter, $confId, $defaultMarkerArr = 0, $marker = 'FEUSER')
    {
        return $this->prepareLabelMarkers('tx_t3users_models_feuser', $formatter, $confId, $defaultMarkerArr, $marker);
    }

    /**
     * Parses the template.
     *
     * @param string $template The HTML-Template
     * @param tx_t3users_models_feuser|null $feuser The fe user
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil $formatter The Formatte to use
     * @param string $confId Pfad der TS-Config des Objekt, z.B. 'listView.event.'
     * @param string $marker Name des Markers für ein Object, z.B. FEUSER
     *        Von diesem String hängen die entsprechenden weiteren Marker ab: ###FEUSER_NAME###
     *
     * @return string das geparste Template
     */

    /**
     * @param string $template das HTML-Template
     * @param \Sys25\RnBase\Domain\Model\DomainModelInterface $item
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil $formatter der zu verwendente Formatter
     * @param string $confId Pfad der TS-Config
     * @param string $marker Name des Markers
     *
     * @return string das geparste Template
     */
    public function parseTemplate($template, &$feuser, &$formatter, $confId, $marker = 'FEUSER')
    {
        if (!is_object($feuser)) {
            $feuser = self::getEmptyInstance('tx_t3users_models_feuser');
        }
        Misc::callHook(
            't3users',
            'feuserMarker_initRecord',
            [
                'item' => &$feuser,
                'template' => &$template,
                'confid' => $confId,
                'marker' => $marker,
                'formatter' => $formatter,
            ],
            $this
        );

        $this->prepareItem($feuser, $formatter->getConfigurations(), $confId);

        // Einstiegspunkt für Kindklassen
        $template = $this->prepareTemplate($template, $feuser, $formatter, $confId, $marker);

        // Es wird das MarkerArray mit den Daten des Records gefüllt.
        $ignore = \Sys25\RnBase\Frontend\Marker\MarkerUtility::findUnusedAttributes($feuser, $template, $marker);

        $markerArray = $formatter->getItemMarkerArrayWrapped(
            $feuser->getRecord(),
            $confId,
            $ignore,
            $marker.'_',
            $feuser->getColumnNames()
        );

        // subparts erzeugen
        $wrappedSubpartArray = $subpartArray = [];
        $this->prepareSubparts(
            $wrappedSubpartArray,
            $subpartArray,
            $template,
            $feuser,
            $formatter,
            $confId,
            $marker
        );

        // Links erzeugen
        $this->prepareLinks(
            $feuser,
            $marker,
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray,
            $confId,
            $formatter,
            $template
        );

        // Gruppen hinzufügen
        if ($this->containsMarker($template, $marker.'_FEGROUPS')) {
            $template = $this->_addGroups($template, $feuser, $formatter, $confId.'group.', $marker.'_FEGROUP');
        }

        // das Template rendern
        $out = self::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        Misc::callHook(
            't3users',
            'feuserMarker_afterSubst',
            [
                'item' => &$feuser,
                'template' => &$out,
                'confid' => $confId,
                'marker' => $marker,
                'formatter' => $formatter,
            ],
            $this
        );

        return $out;
    }

    /**
     * Führt vor dem parsen Änderungen am Model durch.
     *
     * @param \Sys25\RnBase\Domain\Model\DataInterface $item
     * @param \Sys25\RnBase\Configuration\ConfigurationInterface $configurations
     * @param string $confId
     *
     * @return void
     */
    protected function prepareItem(
        \Sys25\RnBase\Domain\Model\DataInterface $item,
        \Sys25\RnBase\Configuration\ConfigurationInterface $configurations,
        $confId
    ) {
        $item->setIsCurrentUser(
            TYPO3::getFEUserUID() == $item->getUid()
        );

        parent::prepareItem($item, $configurations, $confId);
    }

    /**
     * Fügt den Sprecher in das Template ein.
     *
     * @param string $template
     * @param tx_t3users_models_feuser $feuser
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil $formatter
     * @param string $confId
     * @param string $markerPrefix
     *
     * @return string
     */
    private function _addGroups($template, &$feuser, &$formatter, $confId, $markerPrefix)
    {
        $children = $feuser->getGroups();
        $listBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Frontend\Marker\ListBuilder::class);
        $out = $listBuilder->render(
            $children,
            false,
            $template,
            'tx_t3users_util_FeGroupMarker',
            $confId,
            $markerPrefix,
            $formatter
        );

        return $out;
    }

    /**
     * Links vorbereiten.
     *
     * @param tx_t3users_models_feuser $profile
     * @param string $marker
     * @param array $markerArray
     * @param array $wrappedSubpartArray
     * @param string $confId
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil $formatter
     */
    protected function prepareLinks(
        $feuser,
        $marker,
        &$markerArray,
        &$subpartArray,
        &$wrappedSubpartArray,
        $confId,
        $formatter,
        $template
    ) {
        parent::prepareLinks($feuser, $marker, $markerArray, $subpartArray, $wrappedSubpartArray, $confId, $formatter, $template);
        if ($feuser->isDetailsEnabled()) {
            $this->initLink($markerArray, $subpartArray, $wrappedSubpartArray, $formatter, $confId, 'details', $marker, ['feuserId' => $feuser->uid], $template);
        } else {
            $linkMarker = $marker.'_'.strtoupper('details').'LINK';
            $this->disableLink($markerArray, $subpartArray, $wrappedSubpartArray, $linkMarker, false);
        }
    }
}

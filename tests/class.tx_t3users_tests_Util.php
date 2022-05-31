<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <dev@dmk-ebusiness.de>
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
 */

/*
 * benötigte Klassen einbinden
 */

/**
 * Statische Hilfsmethoden für Tests.
 */
class tx_t3users_tests_Util
{
    /**
     * Liefert eine DateiNamen.
     *
     * @param $filename
     * @param $dir
     * @param $extKey
     *
     * @return string
     */
    public static function getFixturePath($filename, $dir = 'tests/fixtures/', $extKey = 't3users')
    {
        return \Sys25\RnBase\Utility\Extensions::extPath($extKey).$dir.$filename;
    }

    /**
     * Ein Basis-Configurations Objekt erstellen.
     */
    public static function getConfigurations()
    {
        $extKey = 't3users';
        \Sys25\RnBase\Utility\Extensions::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$extKey.'/Configuration/TypoScript/setup.typoscript">');

        \Sys25\RnBase\Utility\Misc::prepareTSFE(); // Ist bei Aufruf aus BE notwendig!
        $GLOBALS['TSFE']->config = [];
        $cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);

        $pageTSconfig = \Sys25\RnBase\Backend\Utility\BackendUtility::getPagesTSconfig(0);
        $pageTSconfig = (array) $pageTSconfig['plugin.']['tx_'.$extKey.'.'];
        $qualifier = $pageTSconfig['qualifier'] ? $pageTSconfig['qualifier'] : $extKey;
        $configurations = new \Sys25\RnBase\Configuration\Processor();
        $configurations->init($pageTSconfig, $cObj, $extKey, $qualifier);

        return $configurations;
    }

    /**
     * Setzt eine Vaiable in die Extension Konfiguration.
     * Achtung im setUp sollte storeExtConf und im tearDown restoreExtConf aufgerufen werden.
     *
     * @param string    $sCfgKey
     * @param string    $sCfgValue
     * @param string    $sExtKey
     */
    public static function setExtConfVar($sCfgKey, $sCfgValue, $sExtKey = 't3users')
    {
        // aktuelle Konfiguration auslesen
        $extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey]);
        // wenn keine Konfiguration existiert, legen wir eine an.
        if (!is_array($extConfig)) {
            $extConfig = [];
        }
        // neuen Wert setzen
        $extConfig[$sCfgKey] = $sCfgValue;
        // neue Konfiguration zurückschreiben
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey] = serialize($extConfig);
    }
}

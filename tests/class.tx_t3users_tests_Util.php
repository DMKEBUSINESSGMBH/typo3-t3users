<?php
/**
 * @package tx_t3users
 * @subpackage tx_t3users_tests
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

/**
 * benötigte Klassen einbinden
 */

tx_rnbase::load('tx_rnbase_cache_Manager');
tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_util_Spyc');
tx_rnbase::load('Tx_Rnbase_Backend_Utility');

/**
 * Statische Hilfsmethoden für Tests
 *
 * @package tx_t3users
 * @subpackage tx_t3users_tests
 */
class tx_t3users_tests_Util
{

  /**
   * Liefert eine DateiNamen
   * @param $filename
   * @param $dir
   * @param $extKey
   * @return string
   */
    public static function getFixturePath($filename, $dir = 'tests/fixtures/', $extKey = 't3users')
    {
        return tx_rnbase_util_Extensions::extPath($extKey).$dir.$filename;
    }

/**
 * Ein Basis-Configurations Objekt erstellen
 */
    public function getConfigurations()
    {
        $extKey = 't3users';
        tx_rnbase_util_Extensions::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$extKey.'/static/ts/setup.txt">');

        tx_rnbase::load('tx_rnbase_configurations');
        tx_rnbase::load('tx_rnbase_util_Misc');

        tx_rnbase_util_Misc::prepareTSFE(); // Ist bei Aufruf aus BE notwendig!
        $GLOBALS['TSFE']->config = array();
        tx_rnbase::load('tx_rnbase_util_Typo3Classes');
        $cObj = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());

        $pageTSconfig = Tx_Rnbase_Backend_Utility::getPagesTSconfig(0);
        $pageTSconfig = (array) $pageTSconfig['plugin.']['tx_'.$extKey.'.'];
        $qualifier = $pageTSconfig['qualifier'] ? $pageTSconfig['qualifier'] : $extKey;
        $configurations = new tx_rnbase_configurations();
        $configurations->init($pageTSconfig, $cObj, $extKey, $qualifier);

        return $configurations;
    }

    /**
     * Setzt eine Vaiable in die Extension Konfiguration.
     * Achtung im setUp sollte storeExtConf und im tearDown restoreExtConf aufgerufen werden.
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
            $extConfig = array();
        }
        // neuen Wert setzen
        $extConfig[$sCfgKey] = $sCfgValue;
        // neue Konfiguration zurückschreiben
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey] = serialize($extConfig);
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/tests/class.tx_t3users_tests_Util.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/tests/class.tx_t3users_tests_Util.php']);
}

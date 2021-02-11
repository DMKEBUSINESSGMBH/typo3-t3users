<?php

/*
 * benötigte Klassen einbinden
 */

tx_rnbase::load('tx_rnbase_mod_ExtendedModFunc');

/**
 * Backend Modul für Formulare.
 */
class tx_t3users_mod_FeUser extends tx_rnbase_mod_ExtendedModFunc
{
    /**
     * Method getFuncId.
     *
     * @return  string
     */
    public function getFuncId()
    {
        return 'feuser';
    }

    /**
     * Liefert die Einträge für das Tab-Menü.
     * return array.
     */
    protected function getSubMenuItems()
    {
        $menuItems = [];
        $menuItems[] = tx_rnbase::makeInstance('tx_t3users_mod_handler_ManageFeUser');

        return $menuItems;
    }

    protected function makeSubSelectors(&$selStr)
    {
        return false;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/class.tx_t3users_mod_FeUser.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/class.tx_t3users_mod_FeUser.php'];
}

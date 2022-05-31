<?php

/*
 * benötigte Klassen einbinden
 */

/**
 * Backend Modul für Formulare.
 */
class tx_t3users_mod_FeUser extends \Sys25\RnBase\Backend\Module\ExtendedModFunc
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
        $menuItems[] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_mod_handler_ManageFeUser');

        return $menuItems;
    }

    protected function makeSubSelectors(&$selStr)
    {
        return false;
    }
}

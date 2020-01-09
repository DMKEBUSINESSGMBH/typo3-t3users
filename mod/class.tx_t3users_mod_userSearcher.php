<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche (dev@dmk-ebusiness.de)
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

tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('Sys25\\RnBase\\Configuration\\Processor');
tx_rnbase::load('tx_rnbase_parameters');
tx_rnbase::load('Tx_Rnbase_Backend_Utility');

/**
 * Searcher class for fe users.
 * Wird im Function-Modul verwendet.
 * @deprecated TODO: convert mod_lister_FeUser instead!
 */
class tx_t3users_mod_userSearcher
{
    private $mod;
    private $data;
    private $searchButtonName = 'searchUser';
    private $SEARCH_SETTINGS;
    private $bAllowNonAdmins;

    public function __construct(&$mod, $options = array())
    {
        $this->init($mod, $options);
    }

    private function init($mod, $options)
    {
        $this->options = $options;
        $this->mod = $mod;
        $this->formTool = $this->mod->formTool;
        $this->resultSize = 0;
        $this->data = tx_rnbase_parameters::getPostOrGetParameter('searchdata');

        $this->bAllowNonAdmins = \Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('t3users', 'fullModuleForNonAdmins');

        if (!isset($options['nopersist'])) {
            $searchData = array('termfeuser' => '', 'hiddenfeuser' => '', 'pagemode' => '','uidfeuser' => '');
            $oldSettings = Tx_Rnbase_Backend_Utility::getModuleData($searchData, false, $this->mod->MCONF['name']);
            if ($this->data['termfeuser'] != $oldSettings['termfeuser']) {
                $this->data['uidfeuser'] = '';
            }
            if (strlen($this->data['uidfeuser']) && $this->data['uidfeuser'] != $oldSettings['uidfeuser']) {
                $this->data['termfeuser'] = '';
            }
            $this->SEARCH_SETTINGS = Tx_Rnbase_Backend_Utility::getModuleData(
                $searchData,
                $this->data,
                $this->mod->MCONF['name']
            );
        } else {
            $this->SEARCH_SETTINGS = $this->data;
        }
    }
    /**
     * Liefert true, wenn im aktuellen Request eine Suchanfrage abgesetzt wurde.
     * @return bool
     */
    public function hasSearched()
    {
        return tx_rnbase_parameters::getPostOrGetParameter($this->searchButtonName) != false;
    }
    /**
     * Liefert das Suchformular
     *
     * @param string $label Alternatives Label
     * @return string
     */
    public function getSearchForm($label = '')
    {
        global $LANG;
        $out = '';
        $out .= (strlen($label) ? $label : $LANG->getLL('label_searchterm')).': ';
        $out .= $this->formTool->createTxtInput('searchdata[termfeuser]', $this->SEARCH_SETTINGS['termfeuser'], 20);
        $out .= $this->getFormTool()->createSelectSingleByArray('searchdata[hiddenfeuser]', $this->SEARCH_SETTINGS['hiddenfeuser'], array(0 => $LANG->getLL('label_active_user'), 1 => $LANG->getLL('label_hidden_user')));

        if ($GLOBALS['BE_USER']->isAdmin() || $this->bAllowNonAdmins) {
            $out .= $this->getFormTool()->createSelectSingleByArray('searchdata[pagemode]', $this->SEARCH_SETTINGS['pagemode'], array(1 => $LANG->getLL('label_pagemode_all'), 0 => $LANG->getLL('label_pagemode_current')));
        }
        if ($GLOBALS['BE_USER']->isAdmin() || $this->bAllowNonAdmins) {
            $out .= '&nbsp;'.$LANG->getLL('label_uid').': '.$this->formTool->createTxtInput('searchdata[uidfeuser]', $this->SEARCH_SETTINGS['uidfeuser'], 5);
        }
        // Den Update-Button einfügen
        $out .= '<input type="submit" name="' . $this->searchButtonName . '" value="'.$LANG->getLL('btn_search').'" />';
        // Jetzt noch zusätzlichen JavaScriptcode für Buttons auf der Seite
        $out .= $this->formTool->getJSCode($this->mod->id);

        return $out;
    }
    public function getResultList()
    {
        global $LANG;
        $content = '';
        $searchuid = intval($this->SEARCH_SETTINGS['uidfeuser']);
        $searchterm = trim($this->SEARCH_SETTINGS['termfeuser']);
        $pagemode = intval($this->SEARCH_SETTINGS['pagemode']);
        $pager = tx_rnbase::makeInstance('tx_rnbase_util_BEPager', 'feusrpager', $this->mod->MCONF['name'], $this->mod->id);

        if ($searchuid > 0 && ($GLOBALS['BE_USER']->isAdmin() || $this->bAllowNonAdmins)) {
            // Diese Suche nach der UID hat Vorrang.
            tx_rnbase::load('tx_t3users_models_feuser');
            $item = tx_t3users_models_feuser::getInstance($searchuid);
            if ($item->isValid()) {
                $items[] = $item;
                $label = $LANG->getLL('label_search4uid').' ' . $searchuid;
            }
        } elseif (strlen($searchterm) > 0 || true) {
            $searchhidden = intval($this->SEARCH_SETTINGS['hiddenfeuser']) > 0;
            // BEPager: Zuerst die Anzahl ermitteln
            $options['count'] = 1;
            $this->resultSize = $this->searchFEUser($searchterm, $searchhidden, $pagemode, $options);
            $pager->setListSize($this->resultSize);
            $pager->setOptions($options);
            unset($options['count']);
            $items = $this->searchFEUser($searchterm, $searchhidden, $pagemode, $options);
            $label = $this->resultSize .' '. (($this->resultSize == 1) ? $LANG->getLL('label_founduser') : $LANG->getLL('label_foundusers'));
        }
        $this->showFEUser($content, $label, $items, $pager);

        return $content;
    }
    /**
     * Liefert die Anzahl der gefunden Mitglieder.
     * Funktioniert natürlich erst, nachdem die Ergebnisliste abgerufen wurde.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->resultSize;
    }

    public function searchFEUser($searchterm, $searchhidden, $pagemode, $options = array())
    {
        tx_rnbase::load('tx_t3users_search_builder');
        $fields = array();
        $options['orderby'] = array('FEUSER.USERNAME' => 'asc');
        if (!$searchhidden) {
            $options['enablefieldsfe'] = 1;
        }
        if ($pagemode == 0) {
            // Aktuelle Seite
            $fields['FEUSER.PID'][OP_EQ_INT] = $this->mod->pObj->id;
        }
        tx_t3users_search_builder::buildFeUserFreeText($fields, $searchterm);
        // Zusätzlich nach UID mit suchen, das Funktioniert aber nur, wenn der SearchBuilder die Freitextsuche aktiviert hat
        if (isset($fields[SEARCH_FIELD_JOINED])) {
            $fields[SEARCH_FIELD_JOINED][0]['cols'][] = 'FEUSER.UID';
        }
        $srv = tx_t3users_util_ServiceRegistry::getFeUserService();
        $items = $srv->search($fields, $options);

        return $items;
    }

    public function showFEUser(&$content, $headline, &$items, $pager)
    {
        tx_rnbase::load('tx_t3users_util_Decorator');
        $decor = tx_rnbase::makeInstance('tx_t3users_util_FEUserDecorator');
        $columns['uid'] = array('title' => 'label_uid');
        $columns['username'] = array('title' => 'label_tableheader_username', 'decorator' => $decor);
        $columns['usergroup'] = array('title' => 'label_tableheader_usergroup', 'decorator' => $decor);
        $columns['name'] = array('title' => 'label_name');
        if (intval(\Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('t3users', 'extendTCA'))) {
            $columns['first_name'] = array('title' => 'label_firstname');
            $columns['last_name'] = array('title' => 'label_lastname');
        }

        if ($items) {
            $arr = tx_t3users_util_Decorator::prepareTable($items, $columns, $this->formTool, $this->options);
            $pagerData = $pager->render();
            $out .= '<div class="pager">' . $pagerData['limits'] . ' - ' .$pagerData['pages'] .'</div>';
            $out .= $this->mod->doc->table($arr[0]);
        }
        $content .= $this->mod->doc->section($headline.':', $out, 0, 1, ICON_INFO);
    }
    /**
     * Liefert das FormTool
     *
     * @return tx_dsagbase_mod1_formtool
     */
    public function getFormTool()
    {
        return $this->formTool;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/class.tx_t3users_mod_userSearcher.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/class.tx_t3users_mod_userSearcher.php']);
}

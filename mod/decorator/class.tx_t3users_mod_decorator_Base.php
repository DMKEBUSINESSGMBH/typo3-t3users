<?php
/**
 * @package tx_t3users
 * @subpackage tx_t3users_mod
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Hannes Bochmann <dev@dmk-ebusiness.de>
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

tx_rnbase::load('Tx_Rnbase_Backend_Decorator_BaseDecorator');
tx_rnbase::load('tx_rnbase_mod_Util');

/**
 * Diese Klasse ist für die Darstellung von Elementen im Backend verantwortlich.
 *
 * @package tx_t3users
 * @subpackage tx_t3users_mod
 */
class tx_t3users_mod_decorator_Base extends Tx_Rnbase_Backend_Decorator_BaseDecorator
{
    /**
     *
     * @param   string                  $value
     * @param   string                  $colName
     * @param   array                   $record
     * @param   tx_rnbase_model_base    $item
     */
    public function format(
        $columnValue,
        $columnName,
        array $record,
        \Tx_Rnbase_Domain_Model_DataInterface $entry
    ) {
        $ret = $columnValue;

        switch ($columnName) {
            case 'crdate':
            case 'tstamp':
                $ret = strftime('%d.%m.%y %H:%M:%S', intval($ret));
                break;
            case 'usergroup':
                $ret = self::showUsergroups($entry, $this->getFormTool());
                break;
            case 'actions':
                $ret .= $this->getActions($entry, $this->getActionOptions($entry));
                break;

            default:
                $ret = $ret;
                break;
        }

        return $ret;
    }

    /**
     * Liefert die möglichen Optionen für die actions
     * @param tx_rnbase_model_base $item
     * @return array
     */
    protected function getActionOptions($item = null)
    {
        $cols = array(
            'edit' => '',
            'hide' => '',
            'userswitch' => '',
        );

        $userIsAdmin = is_object($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER']->isAdmin() : 0;
        //admins dürfen auch löschen
        if ($userIsAdmin) {
            $cols['remove'] = '';
        }

        return $cols;
    }

    public static function showUsergroups($feuser, $formTool)
    {
        $ret = '';
        $srv = tx_t3users_util_ServiceRegistry::getFeUserService();
        $items = array();
        $groups = $srv->getFeGroups($feuser);
        foreach ($groups as $group) {
            $items[] = '<li>'.$group->getTitle().' '. $formTool->createEditLink('fe_groups', $group->getUid(), '').'</li>';
        }
        if (!empty($items)) {
            $ret = '<ul class="usergroup">'.implode('', $items).'</ul>';
        }

        return $ret;
    }

    /**
     * @TODO: weitere links integrieren!
     * $options = array('hide'=>'ausblenden,'edit'=>'bearbeiten,'remove'=>'löschen','history'='history','info'=>'info','move'=>'verschieben');
     *
     * @param   tx_rnbase_model_base    $item
     * @param   array                   $options
     * @return  string
     */
    protected function getActions(
        tx_rnbase_model_base $item,
        array $options
    ) {
        $ret = array();
        foreach ($options as $sLinkId => $bTitle) {
            switch ($sLinkId) {
                case 'edit':
                    $ret[] = $this->getFormTool()->createEditLink($item->getTableName(), $item->getUid(), $bTitle);
                    break;
                case 'hide':
                    $ret[] = $this->getFormTool()->createHideLink($item->getTableName(), $item->getUid(), $item->isHidden());
                    break;
                case 'remove':
                    //Es wird immer ein Bestätigungsdialog ausgegeben!!! Dieser steht
                    //in der BE-Modul locallang.xml der jeweiligen Extension im Schlüssel
                    //'confirmation_deletion'. (z.B. mkkvbb/mod1/locallang.xml) Soll kein
                    //Bestätigungsdialog ausgegeben werden, dann einfach 'confirmation_deletion' leer lassen
                    $ret[] = $this->getFormTool()->createDeleteLink($item->getTableName(), $item->getUid(), $bTitle, array('confirm' => $GLOBALS['LANG']->getLL('confirmation_deletion')));
                    break;
                case 'userswitch':
                    $ret[] = sprintf(
                        '<button class="btn btn-default btn-sm" name="hijack[%1$d]" value="1" title="Become this user (%2$s)">%3$s</button>',
                        $item->getUid(),
                        $item->getUsername(),
                        tx_rnbase_mod_Util::getSpriteIcon(
                            'actions-system-backend-user-switch'
                        )
                    );
                    break;
                default:
                    break;
            }
        }

        return sprintf(
            '<span class="actionlist">%s</span>',
            implode(' ', array_filter($ret))
        );
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/decorator/class.tx_t3users_mod_decorator_Base.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/decorator/class.tx_t3users_mod_decorator_Base.php']);
}

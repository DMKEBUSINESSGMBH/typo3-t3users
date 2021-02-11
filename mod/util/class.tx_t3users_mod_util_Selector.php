<?php

tx_rnbase::load('Tx_Rnbase_Backend_Utility');
tx_rnbase::load('tx_rnbase_parameters');
tx_rnbase::load('tx_rnbase_util_TCA');

/**
 * Die Klasse stellt Auswahlmenus zur Verfügung.
 *
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 */
class tx_t3users_mod_util_Selector
{
    /**
     * @var     tx_rnbase_mod_IModule
     */
    private $mod;
    /**
     * @var     Tx_Rnbase_Backend_Form_ToolBox
     */
    private $formTool;

    /**
     * Initialisiert das Objekt mit dem Template und der Modul-Config.
     */
    public function init(tx_rnbase_mod_IModule $module)
    {
        $this->mod = $module;
        $this->formTool = $this->mod->getFormTool();
    }

    /**
     * Gibt einen selector mit den elementen im gegebenen array zurück.
     *
     * @param array $aItems Array mit den werten der Auswahlbox
     * @param string $sDefId ID-String des Elements
     * @param array $aData enthält die Formularelement für die Ausgabe im Screen. Keys: selector, label
     * @param array $aOptions zusätzliche Optionen: label, id
     *
     * @return string selected item
     */
    protected function showSelectorByArray($aItems, $sDefId, &$aData, $aOptions = [])
    {
        $pid = isset($aOptions['pid']) && $aOptions['pid'] ? $aOptions['pid'] : 0;
        $id = isset($aOptions['id']) && $aOptions['id'] ? $aOptions['id'] : $sDefId;

        $selectedItem = array_key_exists('forcevalue', $aOptions) ? $aOptions['forcevalue'] : $this->getValueFromModuleData($id);

        // Build select box items
        $aData['selector'] = Tx_Rnbase_Backend_Utility::getFuncMenu(
            $pid,
            'SET['.$id.']',
            $selectedItem,
            $aItems
        );

        //label
        $aData['label'] = $aOptions['label'];

        // as the deleted fe users have always to be hidden the function returns always false
        //@todo wozu die alte abfrage? return $defId==$id ? false : $selectedItem;
        return $selectedItem;
    }

    /**
     * Gibt einen selector mit den elementen im gegebenen array zurück.
     *
     * @return string selected item
     */
    protected function showSelectorByTCA($sDefId, $table, $column, &$aData, $aOptions = [])
    {
        $items = [];
        if (is_array($aOptions['additionalItems'])) {
            $items = $aOptions['additionalItems'];
        }
        tx_rnbase_util_TCA::loadTCA($table);
        if (is_array($GLOBALS['TCA'][$table]['columns'][$column]['config']['items'])) {
            foreach ($GLOBALS['TCA'][$table]['columns'][$column]['config']['items'] as $item) {
                $items[$item[1]] = $GLOBALS['LANG']->sL($item[0]);
            }
        }

        return $this->showSelectorByArray($items, $sDefId, $aData, $aOptions);
    }

    /**
     * Returns an instance of tx_rnbase_mod_IModule.
     *
     * @return  tx_rnbase_mod_IModule
     */
    protected function getModule()
    {
        return $this->mod;
    }

    /**
     * @return Tx_Rnbase_Backend_Form_ToolBox
     */
    protected function getFormTool()
    {
        return $this->getModule()->getFormTool();
    }

    /**
     * Return requested value from module data.
     *
     * @param   string $key
     *
     * @return  mixed
     *
     * @deprecated tx_rnbase_mod_Util::getModuleValue verwenden
     */
    public function getValueFromModuleData($key)
    {
        // Fetch selected company trade
        $modData = Tx_Rnbase_Backend_Utility::getModuleData([$key => ''], tx_rnbase_parameters::getPostOrGetParameter('SET'), $this->getModule()->getName());
        if (isset($modData[$key])) {
            return $modData[$key];
        }
        // else
        return null;
    }

    /**
     * Setzt einen Wert in den Modul Daten. Dabei werden die bestehenden
     * ergänzt oder ggf. überschrieben.
     *
     * @param   array $aModuleData
     *
     * @return  void
     */
    public function setValueToModuleData($sModuleName, $aModuleData = [])
    {
        $aExistingModuleData = $GLOBALS['BE_USER']->getModuleData($sModuleName);
        if (!empty($aModuleData)) {
            foreach ($aModuleData as $sKey => $mValue) {
                $aExistingModuleData[$sKey] = $mValue;
            }
        }
        $GLOBALS['BE_USER']->pushModuleData($sModuleName, $aExistingModuleData);
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/util/class.tx_t3users_mod_util_Selector.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/util/class.tx_t3users_mod_util_Selector.php'];
}

<?php

tx_rnbase::load('tx_rnbase_mod_IModHandler');

/**
 * Backend Modul Praxisbörse.
 */
class tx_t3users_mod_handler_ManageFeUser implements tx_rnbase_mod_IModHandler
{
    /**
     * Das aktuelle Modul.
     *
     * @var tx_rnbase_mod_IModule
     */
    protected $mod;

    /**
     * Liefert den Extension-Key des Moduls.
     *
     * @return string
     */
    public function getExtensionKey()
    {
        return 't3users';
    }

    /**
     * @param string $template
     * @param tx_rnbase_mod_IModule $mod
     * @param array $options
     *
     * @return string
     */
    public function showScreen($template, tx_rnbase_mod_IModule $mod, $options)
    {
        $markerArray = [];
        $lister = $this->getLister($mod, $options);
        $formTool = $mod->getFormTool();

        // Hier kommen die Daten für das Mod-Template rein
        $markerArray = $lister->renderListMarkers();
        $markerArray['###BUTTON_FEUSER_NEW###'] = $formTool->createNewLink(
            'fe_users',
            $mod->id,
            $GLOBALS['LANG']->getLL('label_add_feuser')
        );

        // mehr Marker per Hook
        tx_rnbase_util_Misc::callHook(
            't3users',
            'mod_feuser_getMoreMarker',
            ['markerArray' => &$markerArray, 'mod' => $mod],
            $this
        );

        $template = tx_t3users_util_LoginAsFEUser::hijackUser().$template;

        tx_rnbase::load('tx_rnbase_util_Templates');
        $out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray);

        return $out;
    }

    /**
     * @param tx_rnbase_mod_IModule $mod
     * @param array $options
     *
     * @return tx_t3users_mod_lister_FeUser
     */
    private function getLister(tx_rnbase_mod_IModule $mod, $options)
    {
        // @TODO: das ist falsch, die ID muss von getSubID geholt werden
        // das würde dann ManageFeUser.listerclass ergeben!
        $lister = $mod->getConfigurations()->get('feuser.listerclass');
        if ($lister) {
            return tx_rnbase::makeInstance($lister, $mod, $options);
        }

        return tx_rnbase::makeInstance('tx_t3users_mod_lister_FeUser', $mod, $options);
    }

    /**
     * Returns a unique ID for this handler. This is used to created the subpart in template.
     *
     * @return string
     */
    public function getSubID()
    {
        return 'ManageFeUser';
    }

    /**
     * Returns the label for Handler in SubMenu. You can use a label-Marker.
     *
     * @return string
     */
    public function getSubLabel()
    {
        return '###LABEL_HANDLER_MANAGEFEUSER###';
    }

    public function handleRequest(
        tx_rnbase_mod_IModule $mod
    ) {
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/handler/class.tx_t3users_mod_handler_ManageFeUser.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/handler/class.tx_t3users_mod_handler_ManageFeUser.php'];
}

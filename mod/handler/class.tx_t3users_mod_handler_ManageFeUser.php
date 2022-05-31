<?php

/**
 * Backend Modul Praxisbörse.
 */
class tx_t3users_mod_handler_ManageFeUser implements \Sys25\RnBase\Backend\Module\IModHandler
{
    /**
     * Das aktuelle Modul.
     *
     * @var \Sys25\RnBase\Backend\Module\IModule
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
     * @param \Sys25\RnBase\Backend\Module\IModule $mod
     * @param array $options
     *
     * @return string
     */
    public function showScreen($template, \Sys25\RnBase\Backend\Module\IModule $mod, $options)
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
        \Sys25\RnBase\Utility\Misc::callHook(
            't3users',
            'mod_feuser_getMoreMarker',
            ['markerArray' => &$markerArray, 'mod' => $mod],
            $this
        );

        $template = tx_t3users_util_LoginAsFEUser::hijackUser().$template;

        $out = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($template, $markerArray);

        return $out;
    }

    /**
     * @param \Sys25\RnBase\Backend\Module\IModule $mod
     * @param array $options
     *
     * @return tx_t3users_mod_lister_FeUser
     */
    private function getLister(\Sys25\RnBase\Backend\Module\IModule $mod, $options)
    {
        // @TODO: das ist falsch, die ID muss von getSubID geholt werden
        // das würde dann ManageFeUser.listerclass ergeben!
        $lister = $mod->getConfigurations()->get('feuser.listerclass');
        if ($lister) {
            return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($lister, $mod, $options);
        }

        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3users_mod_lister_FeUser', $mod, $options);
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
        \Sys25\RnBase\Backend\Module\IModule $mod
    ) {
    }
}

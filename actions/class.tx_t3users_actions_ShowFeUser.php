<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche (dev@dmk-ebusiness.de)
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



tx_rnbase::load('tx_rnbase_action_BaseIOC');
tx_rnbase::load('tx_t3users_models_feuser');


/**
 * Controller for detailview of FE-User
 */
class tx_t3users_actions_ShowFeUser extends tx_rnbase_action_BaseIOC
{
    
  /**
   *
   *
   * @param array_object $parameters
   * @param \Sys25\RnBase\Configuration\Processor $configurations
   * @param array $viewData
   * @return string error msg or null
   */
    public function handleRequest(&$parameters, &$configurations, &$viewData)
    {
        $userSrv = tx_t3users_util_serviceRegistry::getFeUserService();

        // gegenwärtig angemeldeten User ausgeben, wenn Option gesetzt
        if ($configurations->getBool('feuserdetails.currentUser', true, false)) {
            $user = $userSrv->getFeUserWithFallback();
        } else {
            // Look for static user uid
            $uid = intval($configurations->get('feuserdetails.staticUser'));
            if (!$uid) {
                $uid = intval($parameters->offsetGet('feuserId'));
            }
            if (!$uid) {
                $uid = intval($parameters->offsetGet('NK_feuserId'));
            }
            if (!$uid) {
                return $configurations->getCfgOrLL('feuserdetails.nouser');
            }
    
            // Let's get the current fe_user
            $user = tx_t3users_models_feuser::getInstance($uid);
        }
        $viewData->offsetSet('user', $user);

        return null;
    }

  

    public function getTemplateName()
    {
        return 'feuserdetails';
    }
    public function getViewClassName()
    {
        return 'tx_t3users_views_ShowFeUser';
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_ShowFeUser.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_ShowFeUser.php']);
}

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

/**
 * Controller for detailview of FE-User.
 */
class tx_t3users_actions_ShowFeUser extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{
    public function handleRequest(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $parameters = $request->getParameters();
        $configurations = $request->getConfigurations();
        $viewData = $request->getViewContext();

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

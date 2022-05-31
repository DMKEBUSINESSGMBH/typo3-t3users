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
 * Controller für die Listenansicht für FeGruppen.
 */
class tx_t3users_actions_ListFeUsers extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{
    public function handleRequest(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $parameters = $request->getParameters();
        $configurations = $request->getConfigurations();
        $viewData = $request->getViewContext();

        $userSrv = tx_t3users_util_ServiceRegistry::getFeUserService();

        $fields = [];
        $options = ['count' => 1];
        $this->initSearch($fields, $options, $request);
        $listSize = $userSrv->search($fields, $options);
        unset($options['count']);
        // PageBrowser initialisieren
        $pageBrowser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Utility\PageBrowser::class, 'feusers');
        $pageSize = $this->getPageSize($configurations);

        // Wurde neu gesucht?
        if ($parameters->offsetGet('NK_newsearch') || $parameters->offsetGet('newsearch')) {
            // Der Suchbutton wurde neu gedrückt. Der Pager muss initialisiert werden
            $pageBrowser->setState(null, $listSize, $pageSize);
            $configurations->removeKeepVar('newsearch');
        } else {
            $pageBrowser->setState($parameters, $listSize, $pageSize);
        }
        $limit = $pageBrowser->getState();
        $options = array_merge($options, $limit);
        $result = $userSrv->search($fields, $options);

        $viewData->offsetSet('userlist', $result);
        $viewData->offsetSet('pagebrowser', $pageBrowser);

        \Sys25\RnBase\Utility\Misc::callHook(
            't3users',
            'actions_ListFeUsers_afterHandleRequest',
            [
                'viewData' => &$viewData,
                'parameters' => &$parameters,
                'configurations' => &$configurations,
            ],
            $this
        );

        return null;
    }

    /**
     * Liefert die Anzahl der Ergebnisse pro Seite.
     *
     * @param \Sys25\RnBase\Configuration\Processor $configurations
     *
     * @return int
     */
    protected function getPageSize($configurations)
    {
        return intval($configurations->get('feuserlist.feuser.pagebrowser.limit'));
    }

    protected function initSearch(&$fields, &$options, \Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        // Look for static user uid
        $uids = $request->getConfigurations()->get('feuserlist.staticUsers');
        if ($uids) {
            $fields['FEUSER.UID'][OP_IN_INT] = $uids;
        } else {
            $filter = \Sys25\RnBase\Frontend\Filter\BaseFilter::createFilter($request, $this->getConfId());
            $filter->init($fields, $options);
        }

        // Freitextsuche
        // @TODO freitext suche in eigenen filter auslagern
        tx_t3users_search_builder::buildFeUserFreeText($fields, $request->getParameters()->offsetGet('searchfeuser'));
    }

    public function getTemplateName()
    {
        return 'feuserlist';
    }

    public function getViewClassName()
    {
        return 'tx_t3users_views_ListFeUsers';
    }
}

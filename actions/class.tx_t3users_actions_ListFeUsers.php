<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche (nitzsche@das-medienkombinat.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_action_BaseIOC');
tx_rnbase::load('tx_t3users_search_builder');



/**
 * Controller für die Listenansicht für FeGruppen
 *
 */
class tx_t3users_actions_ListFeUsers extends tx_rnbase_action_BaseIOC {

	/**
	 *
	 *
	 * @param array_object $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewData
	 * @return string error msg or null
	 */
	function handleRequest(&$parameters,&$configurations, &$viewData){

		$userSrv = tx_t3users_util_serviceRegistry::getFeUserService();

		$fields = array();
		$options = array('count'=> 1);
//  	$options['debug'] = 1;
		$this->initSearch($fields, $options, $parameters, $configurations);
		$listSize = $userSrv->search($fields, $options);
		unset($options['count']);
		// PageBrowser initialisieren
		$pageBrowser = tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 'feusers');
		$pageSize = $this->getPageSize($parameters, $configurations);

		//Wurde neu gesucht?
		if($parameters->offsetGet('NK_newsearch') || $parameters->offsetGet('newsearch')) {
			// Der Suchbutton wurde neu gedrückt. Der Pager muss initialisiert werden
			$pageBrowser->setState(null, $listSize, $pageSize);
			$configurations->removeKeepVar('newsearch');
		}
		else {
			$pageBrowser->setState($parameters, $listSize, $pageSize);
		}
		$limit = $pageBrowser->getState();
		$options = array_merge($options, $limit);
		$result = $userSrv->search($fields, $options);

		$viewData->offsetSet('userlist', $result);
		$viewData->offsetSet('pagebrowser', $pageBrowser);

		return null;
  }

  /**
   * Liefert die Anzahl der Ergebnisse pro Seite
   *
   * @param array $parameters
   * @param tx_rnbase_configurations $configurations
   * @return int
   */
  protected function getPageSize(&$parameters, &$configurations) {
  	return intval($configurations->get('feuserlist.feuser.pagebrowser.limit'));
  }
  protected function initSearch(&$fields, &$options, &$parameters, &$configurations) {
    // Look for static user uid
    $uids = $configurations->get('feuserlist.staticUsers');
    if($uids) {
  		$fields['FEUSER.UID'][OP_IN_INT] = $uids;
    }
    else {
	  	$filter = tx_rnbase_filter_BaseFilter::createFilter(
  			$parameters, $configurations, $configurations->getViewData(),
  			$this->getConfId()
		);
	  	$filter->init($fields, $options);
    }

  	// Freitextsuche
  	// @TODO freitext suche in eigenen filter auslagern
  	tx_t3users_search_builder::buildFeUserFreeText($fields, $parameters->offsetGet('searchfeuser'));
	}

	function getTemplateName() { return 'feuserlist';}
	function getViewClassName() { return 'tx_t3users_views_ListFeUsers';}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_ListFeUsers.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3users/actions/class.tx_t3users_actions_ListFeUsers.php']);
}

?>